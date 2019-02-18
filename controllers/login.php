<?php
function pageLogic($previousData){

    if(!empty($_POST)){
        if(isset($_POST["idStr"]) && isset($_POST["password"]))
        {
            if(!$_SESSION["user"]->login($_POST["idStr"], $_POST["password"])){
                return ["error" => true, "errorMsg" => "Identifiants invalides."];
            }
        }
    }

    if($_SESSION["user"]->isInitialized())
    {
        header("Location: index.html");
        die();
    }
}
?>
