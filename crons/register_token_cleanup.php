<?php
require_once("conf/config.php");

getDB()->query("DELETE FROM register_token WHERE DATEDIFF(NOW(), date) >= 25;");

?>
