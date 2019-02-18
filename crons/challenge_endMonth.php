<?php
//insert a value in the previous month list composed of the average of absences table etc
require_once("conf/config.php");

$db = getDB();

$userData = $db->query("INSERT INTO challenge(date, member, d_ordure, d_compost, d_tri, d_verre) SELECT LAST_DAY(DATE_ADD(NOW(), INTERVAL -1 MONTH)), a.member, (SUM(d_ordure/members.family_size)/(DAY(LAST_DAY(DATE_ADD(NOW(), INTERVAL -1 MONTH)))-a.value)*a.value) AS d_ordure_ad_avg, (SUM(d_compost/members.family_size)/(DAY(LAST_DAY(DATE_ADD(NOW(), INTERVAL -1 MONTH)))-a.value)*a.value) AS d_compost_ad_avg, (SUM(d_tri/members.family_size)/(DAY(LAST_DAY(DATE_ADD(NOW(), INTERVAL -1 MONTH)))-a.value)*a.value) AS d_tri_ad_avg, (SUM(d_verre/members.family_size)/(DAY(LAST_DAY(DATE_ADD(NOW(), INTERVAL -1 MONTH)))-a.value)*a.value) AS d_verre_ad_avg FROM challenge INNER JOIN members ON challenge.member=members.id INNER JOIN absences a ON members.id = a.member WHERE EXTRACT(MONTH FROM challenge.date) = EXTRACT(MONTH FROM NOW())-1 AND a.month = EXTRACT(MONTH FROM NOW())-1 GROUP BY a.member;");

?>
