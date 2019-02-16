<?php

function pageLogic(){
    $ret = [];
    
    $db = getDB();
    if(isset($_POST["csrf"])) //check only one field, but should be enough to know if form was submitted
    {
        if(csrf($_POST["csrf"])){
            if($_POST["id"] === $_SESSION["user"]->getID())
            {
                
                $_SESSION["user"]->registerFromPOST();
                $_SESSION["user"]->reloadData();
            }else{
                if($_SESSION["user"]->hasCap("admin"))
                {
                    $tmpUser = new User();
                    $id = filter_var($_POST["id"], FILTER_VALIDATE_INT);
                    if($tmpUser->loadData($id))
                    {
                        $tmpUser->registerFromPOST();
                        
                        $admin_role = filter_var($_POST["admin"], FILTER_VALIDATE_BOOLEAN);
                        $tmpUser->setAdmin($admin_role); //TODO check result
                    }else{
                    
                    }
                    //unset($tmpUser);
                }else{
                
                }
            }
        }else{
        
        
        }
    
    }
    
    if(isset($_GET["id"])){
        if(csrf($_GET["token"]))
        {
            if($_SESSION["user"]->hasCap("admin")){
                $id = $_GET["id"];
            }else{
            
            
            }
        
        }else{
        
        }
    
    }else{
        $id = $_SESSION["user"]->getID();
    }
    
    $query = $db->prepare("SELECT * FROM members WHERE id = ?");
    $query->execute([$id]);
    
    $ret["values"] = $query->fetch();
    $ret["other_edit"] = $_SESSION["user"]->getID() != $id;
    $ret["admin"] = $_SESSION["user"]->hasCap("admin");
    
    return $ret;
}

?>
