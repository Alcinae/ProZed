<?php

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
                        "ordures" => array("filter" => FILTER_VALIDATE_INT, "options" => array("default" => 0, "min_range" => 0)),
                        "tri" => array("filter" => FILTER_VALIDATE_INT, "options" => array("default" => 0, "min_range" => 0)),
                        "verre" => array("filter" => FILTER_VALIDATE_INT, "options" => array("default" => 0, "min_range" => 0)),
                        "compost" => array("filter" => FILTER_VALIDATE_INT, "options" => array("default" => 0, "min_range" => 0))
                    ];
                    /*
                    array("options" => array(
    "default" => 0,
    "min_range" => 0
)));
                    
                    */
                        
                        
                        
                    $post_data = filter_input_array(INPUT_POST, $fields);

                    $date = new DateTime($post_data["date"]); 
                    $now = new DateTime("now");
                    $challengeStart = new DateTime($previousData["currentChallenge"]["start"]);
                    if($date > $now){
                        echo "Date in the future.";
                    }elseif($date < $challengeStart){
                        echo "Date before challenge started.";
                    }else{
                        if($post_data["ordures"] < 1 && $post_data["tri"] < 1 && $post_data["verre"] < 1 && $post_data["compost"] < 1){
                           // echo "Null values.";
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

?>
