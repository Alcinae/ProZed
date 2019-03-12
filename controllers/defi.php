<?php

function pageLogic($previousData){
    $ret = [];
    
    
    $db = getDB();
    
     //this is now done in custom_global.php   
    //$chllgQuery = $db->query("SELECT start, end, months_labels FROM challenges_info ORDER BY end DESC LIMIT 1;");
    //$ret["currentChallenge"] = $chllgQuery->fetch();
    $showForm = $_SESSION["user"]->hasCap("Participant");
    if($previousData["currentChallenge"]["isRunning"] && $showForm){
        //////////////////////////////////////////////////////////////////////////
        
        require_once("controllers/partials/d_form.php");
        
        
        //////////////////////////////////////////////////////////////////////////
        
        if(isset($_GET["del"]))
        {
            if(csrf($_GET["token"])){
                    
                $id = filter_var($_GET["del"], FILTER_VALIDATE_INT);
                
                $delQuery = $db->prepare("DELETE FROM challenge WHERE id = :id AND member = :user");
                $delQuery->bindValue(":id", $id);
                $delQuery->bindValue(":user", $_SESSION["user"]->getID());
                $delQuery->execute();
                
            }else{
            
            }
        }
    }

    $queryObj = $db->prepare("SELECT id, date, d_ordure, d_verre, d_tri, d_compost FROM challenge WHERE member = ? ORDER BY date ASC;"); //AND EXTRACT(MONTH FROM date) = EXTRACT(MONTH FROM NOW())
    $queryObj->execute([$_SESSION["user"]->getID()]);
    
    $ret["challengeEntries"] = $queryObj->fetchAll();
    
    $queryObj = $db->prepare("SELECT value FROM absences WHERE member = ? AND EXTRACT(MONTH FROM NOW()) = month;");
    $queryObj->execute([$_SESSION["user"]->getID()]);
    
    $ret["currentAbs"] = $queryObj->fetch()["value"];
    
    $ret["showForm"] = $showForm;
    return $ret;
}
?>
