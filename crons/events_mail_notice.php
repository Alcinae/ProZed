<?php
require_once("vendor/php/autoload.php");
require_once("conf/config.php");
require_once("conf/mail.php");

$db = getDB();

global $root_domain, $prefix;

$loader = new Twig_Loader_Filesystem(__DIR__.'/../views/');
$twig = new Twig_Environment($loader, array(
    'cache' => __DIR__.'/../cache/views/',
));

$result = $db->query("SELECT w.name, w.place, w.date, w.start, w.details, w.image, TIMEDIFF(w.end, w.start) AS time, DATEDIFF(w.date, NOW()) AS in_d, m.email, m.fname, m.lname FROM ateliers w INNER JOIN ateliers_subscribers s ON w.id = s.event INNER JOIN members m ON s.subscriber = m.id WHERE m.consent_email = TRUE AND s.subscribed = TRUE AND (DATEDIFF(w.date, NOW()) = 7 OR DATEDIFF(w.date, NOW()) <= 1 OR DATEDIFF(w.date, NOW()) = 3);");

$mail = getMailer();
$adminsQuery = $db->query("SELECT email, fname, lname FROM members WHERE admin = TRUE AND consent_mail = TRUE;");
$adminsMails = $adminsQuery->fetchAll(); 

foreach($result as $mailEntry){
                    try {

                            
                            $contentData = $mailEntry;
                            //ALERT: if database has been compromised, this can be used to read the php scripts
                            
                            $finfo = new finfo(FILEINFO_MIME_TYPE);
                            $contentData["base64_images"][0]["contentType"] = $finfo->file($mailEntry["image"]);
                            $contentData["base64_images"][0]["data"] = base64_encode(file_get_contents($mailEntry["image"]));
                            
                            $contentData["url"] = "$root_domain$prefix/ateliers.html";
                            $contentData["unsubscribeUrl"] = "$root_domain$prefix/ateliers.html";
                            
                            
                            //Content
                            $mail->isHTML(true);  // Set email format to HTML
                            $mail->Subject = 'Atelier Luluzed: '.$contentData["name"];
                            $mail->Body    = $twig->render("email/template_events.html", $contentData); 
                            $mail->AltBody = 'Bonjour '.$contentData["fname"]." ".$contentData["lname"].'! L\'évènement "'.$contentData["name"].'" se déroule dans '.$contentData["in_d"].' jour(s). Rendez-vous sur l\'application pour plus d\'informations ! ';

                            $membersQuery = $db->query("SELECT email FROM members WHERE consent_mail = TRUE;");
                            foreach($membersQuery->fetchAll() as $itm)
                            {
                                foreach($adminsMails as $itm2)
                                {
                                    $mail->addReplyTo($itm2["email"], $itm2["fname"]." ".strtoupper($itm2["lname"]));
                                }
                                $mail->addAddress($itm["email"]);
                                $mail->send();
                                $mail->clearAdresses();
                            }
                        //echo 'Message has been sent';
                        } catch (Exception $e) {
                        //echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
                        }
}
?>
