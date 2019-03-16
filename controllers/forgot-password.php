<?php
require("conf/mail.php");

function pageLogic($previousData){

    global $root_domain, $prefix, $twig;
    if($_SESSION["user"]->isInitialized())
    {
        header("Location: index.html");
        die();
    }

    $ret = [];
    $db = getDB();
    
        if(!empty($_GET["token"]) || !empty($_POST["token"]))
        {
            $token = (!empty($_GET["token"])) ? urlencode($_GET["token"]) : $_POST["token"];
            $checkQuery = $db->prepare("SELECT uuid, member FROM passwd_recover_tokens WHERE uuid = ?;");
            $checkQuery->execute([$token]);
            $uuid_data = $checkQuery->fetchAll();
            
            
            
            if(count($uuid_data) === 1)
            {
                $ret["token"] = $uuid_data[0]["uuid"];
                $ret["showPasswordForms"] = true;
                
                if(isset($_POST["password"])){
                    
                    if($_POST["password"] === $_POST["password2"]){
                    
                        $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
                        unset($_POST["password"]);
                        unset($_POST["password2"]);
                        
                        $updateQuery = $db->prepare("UPDATE members SET password = :password WHERE id = :member;");
                        $updateQuery->bindValue(":password", $password);
                        $updateQuery->bindValue(":member", $uuid_data[0]["member"]);
                        
                        if($updateQuery->execute()){
                            $cleanupQuery = $db->prepare("DELETE FROM passwd_recover_tokens WHERE uuid = ?");
                            $cleanupQuery->execute([$uuid_data[0]["uuid"]]);
                            
                            $ret["passwordChanged"] = true;
                            $ret["showPasswordForms"] = false;
                        }
                    }else{
                        //password do not match
                        $ret["error"] = "Mots de passe non identiques.";
                    }
                

                }
                
                

            }else{
                $ret["error"] = "Le lien n'est pas valide.";
            }
        }else{
            if(isset($_POST["email"]) && csrf($_POST["csrf"]))
            {
                
                $checkQuery = $db->prepare("SELECT id, fname, email FROM members WHERE email = ?;");
                $checkQuery->execute([$_POST["email"]]);
                $data = $checkQuery->fetchAll();
                if(count($data) === 1)
                {
                    $token = preg_replace("/[^A-Za-z0-9 ]/", '', base64_encode(random_bytes(10)));
                
                    try {
                            $insertQuery = $db->prepare("INSERT INTO passwd_recover_tokens (uuid, member, date) VALUES(:uuid, :member, NOW());");
                            $insertQuery->bindValue(":uuid", $token);
                            $insertQuery->bindValue(":member", $data[0]["id"]);
                            $insertQuery->execute();
                    
                            $token = urlencode($token);
                    
                            $mail = getMailer();
                            
                            $adminsQuery = $db->query("SELECT email, fname, lname FROM members WHERE admin = TRUE AND consent_email = TRUE;");
                            $adminsMails = $adminsQuery->fetchAll();
                            
                            //Content
                            $mail->isHTML(true);                                  // Set email format to HTML
                            $mail->Subject = 'Luluzed: Mot de passe oublié';
                            
                            $contentData["url"] = "$root_domain$prefix/forgot-password.html?token=$token";
                            
                            $mail->Body    = $twig->render("email/template_password.html", $contentData); //TODO HTML body 
                            $mail->AltBody = "Vous recevez ce message car une demande de réinitilisation de mot de passe à été demandée pour votre compte. Pour changer celui-ci, visitez {$contentData["url"]}. Si cela vous n'êtes pas a l'origine de ceci, vous pouvez ignorer ce mail. Pour plus de précaution changez votre mot de passe.";


                            foreach($adminsMails as $itm)
                            {
                                $mail->addReplyTo($itm["email"], $itm["fname"]." ".strtoupper($itm["lname"]));
                            }
                            
                            $mail->addAddress($data[0]["email"]);
                            
                            
                            $mail->send();
                            //echo "OK ! (penser a decommenter le mail->send)";
                            
                            
                        //echo 'Message has been sent';
                        } catch (Exception $e) {
                          echo "error.",$e->getMessage();;
                        }
                
                }
                
                //Anyway do not provide information that could be used by hackers. They could still use timing attacks though.
                $ret["success"] = true;
            }
            else
            {
                $ret["success"] = false;
            }
        }

    
        
    return $ret;
}
?>
