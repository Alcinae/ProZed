<?php

function pageLogic($previousData){
    $ret = [];
    
    
    $db = getDB();
    
     //this is now done in custom_global.php   
    //$chllgQuery = $db->query("SELECT start, end, months_labels FROM challenges_info ORDER BY end DESC LIMIT 1;");
    //$ret["currentChallenge"] = $chllgQuery->fetch();
    
    if($previousData["currentChallenge"]["isRunning"]){
        if(!empty($_POST))
        {
            if(csrf($_POST["csrf"])){
                if(isset($_POST["abs"])){
                    $data = filter_var($_POST["abs"], FILTER_VALIDATE_INT);
                    if($data >= 0 && $data <= 31){
                        $insertQuery = $db->prepare("INSERT INTO absences (member, month, value) VALUES(:member, EXTRACT(MONTH FROM NOW()), :value) ON DUPLICATE KEY UPDATE value=:value2");
                        $insertQuery->bindValue(":member", $_SESSION["user"]->getID());
                        $insertQuery->bindValue(":value", $data);
                        $insertQuery->bindValue(":value2", $data);
                        $insertQuery->execute();
                    }else{
                    //error
                    }
                }else{
                    $fields = [
                        "date" => FILTER_SANITIZE_STRING,
                        "ordures" => FILTER_VALIDATE_INT,
                        "tri" => FILTER_VALIDATE_INT,
                        "verre" => FILTER_VALIDATE_INT,
                        "compost" => FILTER_VALIDATE_INT,
                    ];
                        
                        
                        
                    $post_data = filter_input_array(INPUT_POST, $fields);

                    $date = new DateTime($post_data["date"]); 
                    $now = new DateTime("now");
                    $challengeStart = new DateTime($previousData["currentChallenge"]["start"]);
                    if($date > $now){
                        echo "Date in the future.";
                    }elseif($date < $challengeStart){
                        echo "Date before challenge started.";
                    }else{
                        if($post_data["ordures"] < 0 || $post_data["tri"] < 0 || $post_data["verre"] < 0 || $post_data["compost"] < 0){
                           // echo "Negative values.";
                        //TODO error
                        }else{
                            $query = $db->prepare("INSERT INTO challenge(date, member, d_ordure, d_compost, d_tri, d_verre) VALUES(:date, :member, :ordure, :compost, :tri, :verre);");
                            $query->bindValue(":date", $date->format("Y-m-d H:m:s"));
                            $query->bindValue(":member", $_SESSION["user"]->getID());
                            $query->bindValue(":ordure",  $post_data["ordures"]);
                            $query->bindValue(":compost",  $post_data["compost"]);
                            $query->bindValue(":tri",  $post_data["tri"]);
                            $query->bindValue(":verre",  $post_data["verre"]);
                            $query->execute();      
                        }
                    }
                }
            }else{
            //csrf error
            }
        }
        
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

    $queryObj = $db->prepare("SELECT id, date, d_ordure, d_verre, d_tri, d_compost FROM challenge WHERE member = ? AND EXTRACT(MONTH FROM date) = EXTRACT(MONTH FROM NOW());");
    $queryObj->execute([$_SESSION["user"]->getID()]);
    
    $ret["challengeEntries"] = $queryObj->fetchAll();
    
    $queryObj = $db->prepare("SELECT value FROM absences WHERE member = ? AND EXTRACT(MONTH FROM NOW()) = month;");
    $queryObj->execute([$_SESSION["user"]->getID()]);
    
    $ret["currentAbs"] = $queryObj->fetch()["value"];
    
    return $ret;
}
?>
