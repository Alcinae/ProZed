<?php
function pageLogic(){
    $ret = [];
    $db = getDB();
    $queryObj = $db->prepare("SELECT id, image, date, name, details, start, end, place, s.subscribed FROM ateliers LEFT JOIN ateliers_subscribers s ON s.event = ateliers.id AND s.subscriber = ? ORDER BY date;"); //TODO need JOIN to get if subscribed
    
    $queryObj->execute([$_SESSION["user"]->getID()]);
    
    
    $ret["workshops"] = $queryObj->fetchAll();
    //var_dump($ret["workshops"]);
    return $ret;
}

?>
