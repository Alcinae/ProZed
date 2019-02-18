<?php

require("conf/mail.php");

global $twig, $root_domain, $prefix;

    function pageLogic($previousData){ //remember, only admins can access this page, index.php takes care of that (if configured to, as it is here)
        $ret = [];
        $db = getDB();
        
        
        if(!empty($_POST))
        {
        
            if(csrf($_POST["csrf"]))
            {
                $allowed_styles = ["info", "success", "warning", "danger", "primary", "secondary", "dark", "light"];
                
                $fields = [
                    "message" => FILTER_SANITIZE_STRING,
                    "style" => FILTER_SANITIZE_STRING,
                    "expire_date" => FILTER_SANITIZE_STRING,
                    "expire_time" => FILTER_SANITIZE_STRING,
                    "mail_notice" => FILTER_VALIDATE_BOOLEAN,
                    "expire" => FILTER_VALIDATE_BOOLEAN
                ];
                
                
                
                $post_data = filter_input_array(INPUT_POST, $fields);
                //var_dump($post_data);
                if(!in_array($post_data["style"], $allowed_styles))
                {
                    $post_data["style"] = "secondary";
                }
                $style = "alert-".$post_data["style"];
                
                $query = $db->prepare("INSERT INTO news(message, expire, style) VALUES(:message, :expire, :style);");
                $query->bindValue(":message", $post_data["message"]);
                $query->bindValue(":style", $style);
                $edate = NULL;
                if($post_data["expire"])
                {
                   $edate = date('Y-m-d H:i', strtotime("{$post_data["expire_date"]} {$post_data["expire_time"]}"));
                   //$edate = "{$post_data["date"]} {$post_data["time"]}";
                }
                $query->bindValue(":expire", $edate);
                $query->execute();
                //TODO improve this
                
                if($post_data["mail_notice"]){
                    try {
                        $mail = getMailer();
                        $adminsQuery = $db->query("SELECT email, fname, lname FROM members WHERE admin = TRUE AND consent_mail = TRUE;");
                        $adminsMails = $adminsQuery->fetchAll();

                        
                        //Content
                        $mail->isHTML(true);                                  // Set email format to HTML
                        $mail->Subject = 'Luluzed: Vous avez reçu un message.';
                        
                        $contentData["message"] = $post_data["message"];
                        $contentData["url"] = "$root_domain$prefix/index.html";
                        $contentData["unsubscribeUrl"] = "$root_domain$prefix/membre_edit.html";
                        
                        $mail->Body    = $twig->render("email/template_notice.html", $contentData); //TODO HTML body 
                        $mail->AltBody = 'Vous avez reçu une notification :  '.$post_data["message"];

                        $membersQuery = $db->query("SELECT email FROM members WHERE consent_mail = TRUE;");
                        foreach($membersQuery->fetchAll() as $itm)
                        {
                            foreach($adminsMails as $itm)
                            {
                                $mail->addReplyTo($itm["email"], $itm["fname"]." ".strtoupper($itm["lname"]));
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
                
            }else{
                echo "Invalid csrf.";
            }
        }
        
        if(isset($_GET["del"]))
        {
        
            if(csrf($_GET["token"]))
            {
                $id = filter_var($_GET["del"], FILTER_VALIDATE_INT);
                
                $delQuery = $db->prepare("DELETE FROM news WHERE id = :id");
                $delQuery->bindValue(":id", $id);
                $delQuery->execute();
            }else{
            
            }
        
        }
        
        
        
        $queryObj = $db->query("SELECT id, message, expire, style FROM news ORDER BY expire DESC");
    
        $ret["newsList"] = $queryObj->fetchAll();
        return $ret;
    }
?>
