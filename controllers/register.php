<?php

function pageLogic(){
    if($_SESSION["user"]->isInitialized())
    {
        header("Location: index.html");
        die();
    }
    $pageData = [];
    if(!empty($_POST) && !empty($_GET["token"]))
    {
        $result = $_SESSION["user"]->registerFromPOST($_GET["token"]);
        if($result == true){
            header("Location: login.html");
            die();
        }else{
            $pageData["error"] = true;
            $pageData["errorMsg"] = $result[1];
        }
    }else{
    
    }
    return $pageData;
}
?>
