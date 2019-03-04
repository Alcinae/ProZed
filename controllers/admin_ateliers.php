<?php
function pageLogic($previousData){
    $ret = [];
    
    $uploadFolder = "./userData/workshops/";
    
    
    $db = getDB();
        if(!empty($_POST))
        {
            //var_dump($_POST);
            if(csrf($_POST["csrf"]))
            {
                
                $fields = [
                    "name" => FILTER_SANITIZE_STRING,
                    "place" => FILTER_SANITIZE_STRING,
                    "date" => FILTER_SANITIZE_STRING,
                    "start" => FILTER_SANITIZE_STRING,
                    "end" => FILTER_SANITIZE_STRING,
                    "desc" => FILTER_SANITIZE_STRING
                ];
                
                
                $post_data = filter_input_array(INPUT_POST, $fields);
                
                
                $date = date_parse($post_data["date"]);
                if(checkdate($date["month"], $date["day"], $date["year"])){
                
                    if((isValidFullTime($post_data["start"]) && isValidFullTime($post_data["end"])) || (isValidTime($post_data["start"]) && isValidTime($post_data["end"]))){
                    
                        $imageName = "default";
                        try {
                        
                            if (
                                !isset($_FILES['image']['error']) ||
                                is_array($_FILES['image']['error'])
                            ) {
                                throw new RuntimeException('Erreur interne.');
                            }

                           
                            switch ($_FILES['image']['error']) {
                                case UPLOAD_ERR_OK:
                                    break;
                                case UPLOAD_ERR_NO_FILE:
                                    throw new RuntimeException('Aucun fichier.');
                                case UPLOAD_ERR_INI_SIZE:
                                case UPLOAD_ERR_FORM_SIZE:
                                    throw new RuntimeException('Fichier trop grand.');
                                default:
                                    throw new RuntimeException('Erreur.');
                            }

                            if ($_FILES['image']['size'] > 1000000) {
                                throw new RuntimeException('Fichier trop grand.');
                            }

                            $finfo = new finfo(FILEINFO_MIME_TYPE);
                            if (false === $ext = array_search(
                                $finfo->file($_FILES['image']['tmp_name']),
                                array(
                                    'jpg' => 'image/jpeg',
                                    'png' => 'image/png',
                                    'gif' => 'image/gif',
                                ), true)) 
                            {
                                throw new RuntimeException('Type de fichier invalide.');
                            }

                            $imageName = $uploadFolder.sha1_file($_FILES['image']['tmp_name']).".".$ext;
                            if (!move_uploaded_file($_FILES['image']['tmp_name'], $imageName)) {
                                throw new RuntimeException('Impossible d\'effectuer le transfert de fichier..');
                            }


                        } catch (RuntimeException $e) {

                            //echo $e->getMessage();
                            //file upload error.

                        }
                        
                        $query = $db->prepare("INSERT INTO ateliers(name, place, date, start, end, details, image) VALUES(:name, :place, :date, :start, :end, :details, :image);");
                        $query->bindValue(":name", $post_data["name"]);
                        $query->bindValue(":place", $post_data["place"]);
                        $query->bindValue(":date", $post_data["date"]);
                        $query->bindValue(":start", $post_data["start"]);
                        $query->bindValue(":end", $post_data["end"]);
                        $query->bindValue(":details", $post_data["desc"]);
                        $query->bindValue(":image", $imageName);
                    
                        $query->execute();
                       
                    }else{
                        //invalid times
                       // echo "Invalide times";
                    }
                }else{
                    //invalid date
                }
                
            }else{
            
            }
        }
        
        if(isset($_GET["del"]))
        {
        
            if(csrf($_GET["token"]))
            {
                $id = filter_var($_GET["del"], FILTER_VALIDATE_INT);
                
                $infoQuery = $db->prepare("SELECT image FROM ateliers WHERE id = :id;");
                $infoQuery->bindValue(":id", $id);
                $infoQuery->execute();
                $image = $infoQuery->fetch();
                

                
                $delQuery = $db->prepare("DELETE ateliers, ateliers_subscribers FROM ateliers LEFT JOIN ateliers_subscribers ON ateliers_subscribers.event = ateliers.id WHERE ateliers.id = :id;");
                $delQuery->bindValue(":id", $id);
                $delQuery->execute();
                
                if(file_exists($image["image"])){
                    unlink($image["image"]);
                }
            }else{
            
            }
        
        }
    
    
    
    $queryObj = $db->query("SELECT id, image, date, name, details, start, end, place FROM ateliers ORDER BY date;"); 
    
    
    $ret["workshops"] = $queryObj->fetchAll();
    return $ret;
}

?>
