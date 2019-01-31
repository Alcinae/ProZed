<?php
function pageLogic(){
    $db = getDB();
    $ret;
    $ret["usersData"] = [];

    $query = "SELECT members.id, members.lname, members.family_size, members.city, SUM(challenge.d_tri/members.family_size) as d_tri, SUM(challenge.d_verre/members.family_size) as d_verre, SUM(challenge.d_compost/members.family_size) as d_compost, SUM(challenge.d_ordure/members.family_size) as d_ordure FROM challenge INNER JOIN members ON challenge.member=members.id WHERE EXTRACT(MONTH FROM NOW()) = EXTRACT(MONTH FROM challenge.date) GROUP BY members.id, EXTRACT(MONTH FROM challenge.date)";

    $queryObj = $db->query($query);
    
  

    $ret["usersData"] = $queryObj->fetchAll();
    
    
    return $ret;
}
?>
