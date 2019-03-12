<?php

function pageLogic($previousData){
    if($_SESSION["user"]->isInitialized())
    {
        header("Location: index.html");
        die();
    }
    $pageData = [];
    if(!empty($_POST) && !empty($_GET["token"]))
    {
        $db = getDB();
        $query = $db->prepare("SELECT uuid,role FROM register_token WHERE uuid=:uuid");
        $query->bindValue(':uuid', urldecode($_GET["token"]), PDO::PARAM_STR);
        $query->execute();
        $uuid_data = $query->fetchAll();
        
        if(count($uuid_data) != 1)
        {
            $pageData["error"] = true;
            $pageData["errorMsg"] = "Veuillez suivre le lien d'inscription tel qu'il est ecrit dans le mail.";
        }else{
            //echo "1.";
            $result = $_SESSION["user"]->registerFromPOST($uuid_data[0]["role"]);
         
            if($result == true){
                //echo "2.";
                $query = $db->prepare("DELETE FROM register_token WHERE uuid = ?");
                $query->execute([$uuid_data[0]["uuid"]]);
                
                header("Location: login.html");
                die();
            }else{
                $pageData["error"] = true;
                $pageData["errorMsg"] = $result[1];
            }
        
        }
        //    return gen_error("", "Veillez suivre le lien d'inscription tel qu'il est ecrit dans le mail.");
        
        unset($query);

    }else{
    
    }
    $pageData["tokenExists"] = isset($_GET["token"]);
    
    return $pageData;
}
?>
