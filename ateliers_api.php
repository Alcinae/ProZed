<?php
require_once("conf/config.php");
require_once("includes/commons.php");
require_once("includes/sys_account.php");
session_start();
header('Content-Type: application/json');

$db = getDB();
$json = [];

//DEBUG
$_POST["token"] = csrf();
//$_POST["id"] = 6;
//$_POST["val"] = true;

if($_SESSION["user"]->hasCap("member")){
    if(csrf($_POST["token"])){
    
        $fields = [
                    "id" => FILTER_VALIDATE_INT,
                    "val" => FILTER_VALIDATE_BOOLEAN
                ];
                
                
                
        $post_data = filter_input_array(INPUT_POST, $fields);
        
        
        //because php's PDO still has the bug (in 7.0) which prevent passing bool to mysql when prepared request emulation is off, I must convert it to int. And yes   PDO::PARAM_BOOL does nothing !
        $post_data["val"] = (int) $post_data["val"];
        
        
        //var_dump($post_data);
        $insertQuery = $db->prepare("INSERT INTO ateliers_subscribers (subscriber, subscribed, event) VALUES(:member, :status, :workshop) ON DUPLICATE KEY UPDATE subscribed=:status2");
        $insertQuery->bindValue(":member", $_SESSION["user"]->getID());
        $insertQuery->bindValue(":status", $post_data["val"]);
        $insertQuery->bindValue(":status2", $post_data["val"]);
        $insertQuery->bindValue(":workshop", $post_data["id"]);

        $insertQuery->execute();
        
        $getQuery = $db->prepare("SELECT subscribed FROM ateliers_subscribers WHERE subscriber = :member AND event = :workshop");
        $getQuery->bindValue(":member", $_SESSION["user"]->getID());
        $getQuery->bindValue(":workshop", $post_data["id"]);
        $getQuery->execute();
        
        $json["val"] = (bool) $getQuery->fetch()["subscribed"];
        
    }else{
        $json["error"] = "Erreur csrf.";
    }
}else{
    $json["error"] = "Acces refuse.";
}

echo json_encode($json);

?>
