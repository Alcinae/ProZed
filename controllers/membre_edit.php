<?php

function pageLogic($previousData){
    $ret = [];
    
    $db = getDB();
    if(isset($_POST["csrf"])) //check only one field, but should be enough to know if form was submitted
    {
        if(csrf($_POST["csrf"])){
            if($_POST["id"] == $_SESSION["user"]->getID()) //TODO check user old password
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
                        $retval = $tmpUser->registerFromPOST($tmpUser->getRole(), false);
                        //var_dump($retval);
                        $admin_role = 0;
                        if(isset($_POST["admin"]))
                            $admin_role = filter_var($_POST["admin"], FILTER_VALIDATE_BOOLEAN);
                            
                        $tmpUser->setAdmin($admin_role); //TODO check result
                    }else{
                        echo "error";
                    }
                    //unset($tmpUser);
                }else{
                    echo "admin error !";
                }
            }
        }else{
        
            echo "csrf error !";
        }
        $_GET["token"] = csrf();
    
    }
    $id = -1;
    if(isset($_GET["id"])){
        //if(csrf($_GET["token"]))
        //{
            if($_SESSION["user"]->hasCap("admin")){
                $id = $_GET["id"];
            }else{
            
            
            }
        
       // }else{
        
        //}
    
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
