<?php
require_once("includes/sys_account.php");
session_start();
require_once("conf/config.php");
header('Content-Type: application/json');

if(!$_SESSION["user"]->hasCap("member")) die("{'error':'Acces refuse'}");

$db = getDB();

$query = $db->query("SELECT fname, latitude, longitude, email FROM locations INNER JOIN members ON members.id = locations.member;");
$query2 = $db->prepare("SELECT latitude, longitude FROM locations WHERE member = ?;");
$query2->execute([$_SESSION["user"]->getID()]);

echo json_encode(["user" => $query2->fetch(),"others" => $query->fetchAll()]);
?>
