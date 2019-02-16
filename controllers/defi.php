<?php

function pageLogic(){
    $ret = [];
    
    
    $db = getDB();
    
        
    $chllgQuery = $db->query("SELECT start, end, months_labels FROM challenges_info ORDER BY end DESC LIMIT 1;");
    $ret["currentChallenge"] = $chllgQuery->fetch();
    
    //TODO allow submitting values
    
    if(!empty($_POST))
    {
        if(csrf($_POST["csrf"])){
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
            $challengeStart = new DateTime($ret["currentChallenge"]["start"]);
            if($date > $now){
                echo "Date in the future.";
            }elseif($date < $challengeStart){
                echo "Date before challenge started.";
            }else{
                if($post_data["ordures"] < 0 || $post_data["tri"] < 0 || $post_data["verre"] < 0 || $post_data["compost"] < 0){
                    echo "Negative values.";
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

    $queryObj = $db->prepare("SELECT id, date, d_ordure, d_verre, d_tri, d_compost FROM challenge WHERE member = ? AND EXTRACT(MONTH FROM date) = EXTRACT(MONTH FROM NOW());");
    $queryObj->execute([$_SESSION["user"]->getID()]);
    
    $ret["challengeEntries"] = $queryObj->fetchAll();
    
    return $ret;
}
?>
