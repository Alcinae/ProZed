 <?php
require_once("conf/config.php");

$db = getDB();
//$db->query("DELETE FROM ateliers WHERE date < NOW() AND end < EXTRACT(TIME FROM NOW());");
$db->query("DELETE FROM news WHERE expire <= NOW() AND expire != NULL;");

$db->query("DELETE ateliers, ateliers_subscribers FROM ateliers LEFT JOIN ateliers_subscribers ON ateliers_subscribers.event = ateliers.id WHERE ateliers.date < NOW() AND ateliers.end < EXTRACT(HOUR_SECOND FROM NOW());");
?>
