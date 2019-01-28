<?php
require_once("conf/config.php");
require_once("includes/commons.php");
require_once("vendor/php/autoload.php");
require_once("includes/sys_account.php");
session_start();

//$avgColor = Color::fromRgbInt(0,255,89);
//$avgColor2 = Color::fromRgbInt(0,44,88);

$pickedColor = [];    

    function pickColor($average){
    
    }

    //two templates used to build the json objects
    
    $dataElmTmpl = [
        "label" => "PERSONNE (Kgs/Pers)",

        "lineTension" => 0.3,

        "backgroundColor" => "rgba(R,G,B,A)",

        "borderColor" => "rgba(R,G,B,A)",

        "pointRadius" => 5,

        "pointBackgroundColor" => "rgba(R,G,B,A)",

        "pointBorderColor" => "rgba(R,G,B,A)",

        "pointHoverRadius" => 5,

        "pointHoverBackgroundColor" => "rgba(R,G,B,A)",

        "pointHitRadius" => 50,

        "pointBorderWidth" => 2,

        "data" => []
    ];
    
    $dataFrameTmpl = [
    
        "data" => [
            //CHANNELS
        ],
        "yScaleTicks" => [
            "min" => 0,
            "max" => 60
        ]
    ];
    
//because on the graphs the months are not starting with january and some month are invalid, we use this map. The value is the index/position it should be in an array.
$month_remap = ["11" => 0, "12" => 1, "1" => 2, "2" => 3, "3" => 4, "4" => 5, "5" => 6];

header('Content-Type: application/json');

if(!$_SESSION["user"]->hasCap("member"))
{
    die("{\"error\": \"Acces refuse\"}");
}

$db = getDB();

$data_out = [];
$allowed_channels = ["tri", "verre", "ordure", "compost"];

//$_POST["channel"] = ["tri"];
//$_POST["from"] = [6];

//foreach($_POST as $elm){
 //   array_push($data_out, $elm);
//}

//TODO remove once testing done
$_POST["channel"] = ["tri", "verre", "ordure", "compost"];
$_POST["from"] = [6];

if(isset($_POST["channel"]) && isset($_POST["from"]))
{
    //TODO validate "from" and/or "channel"
    $channel = $_POST["channel"];
    $subjects = $_POST["from"];
    
    $qMarks_subjects = str_repeat('?,', count($subjects) - 1) . '?';
    //$channelColumns = "challenge.d_tri, challenge.d_verre, challenge.d_ordure, challenge.d_compost, AVG( challenge.d_verre) AS average"; //TODO procedurally gen this (not including unselected channels)
    $channelColumns = "";
    $avgColumns = "";
    //$groupByList = "";
    $errorOffset = 0;
    for ($i = 0; $i < count($channel); $i++) {
        
        if(in_array($channel[$i], $allowed_channels)){ //NOTE this is also where the DIVIDE BY PERSON is built into the query
            $field = "challenge.d_".$channel[$i];
            $channelColumns .= "SUM(".$field."/members.family_size) AS {$channel[$i]}";
            //$groupByList .= $field;
            
            $avgColumns .= "AVG(vals.{$channel[$i]}/m.family_size) AS {$channel[$i]}";

            if($i+$errorOffset != count($channel)-1){
                $avgColumns .=", ";
                $channelColumns .=", ";
                //$groupByList .= ", ";
            }
            
        }else{
            $errorOffset++;
        }
    }
    //$channelColumns = "challenge.d_tri, challenge.d_verre, challenge.d_ordure, challenge.d_compost, AVG( challenge.d_verre) AS average"; //TODO procedurally gen this (not including unselected channels)
    
    //TODO, check :
    //https://stackoverflow.com/questions/35825115/mysql-average-of-some-rows-with-special-weights
    
    //$query = "SELECT challenge.id, members.lname, challenge.date, ${channelColumns} FROM challenge INNER JOIN members ON challenge.member=members.id WHERE members.id IN (${qMarks_subjects}) GROUP BY challenge.date, challenge.id, members.lname, ${groupByList};";
    
    $sumQuery = "SELECT members.id, members.lname, EXTRACT(MONTH FROM challenge.date) as month, ${channelColumns} FROM challenge INNER JOIN members ON challenge.member=members.id WHERE members.id IN (${qMarks_subjects}) GROUP BY members.id, EXTRACT(MONTH FROM challenge.date);";
    
    $querySum = $db->prepare($sumQuery);
    //for($channel as $value) //TODO 
    //{
    //$averageQuery = "SELECT EXTRACT(MONTH FROM c.date) as month, ${avgColumns} FROM challenge c INNER JOIN members m ON c.member=m.id GROUP BY EXTRACT(MONTH FROM c.date);";  //DO NOT WORK this do not make the average of the sums
    $averageQuery = "SELECT month, ${avgColumns} FROM ( SELECT members.id, members.lname, challenge.member, EXTRACT(MONTH FROM challenge.date) as month, ${channelColumns} FROM challenge INNER JOIN members ON challenge.member=members.id GROUP BY members.id, EXTRACT(MONTH FROM challenge.date) ) vals INNER JOIN members m ON vals.member=m.id GROUP BY month; ";
    $queryAverage = $db->query($averageQuery);
    //}
    //do another query with "SELECT AVG(column) AS average" to get average
    
   // $average = $db->query("SELECT AVG(${var})");
   

    $querySum->execute($subjects);//TODO
    
    
    $data = $querySum->fetchAll();
    $data2 = $queryAverage->fetchAll();
    
  
    //var_dump($data);
    //var_dump($data2);

    
    $data_out = $dataFrameTmpl;
    //when the js display the json data, it does not care to whom the data belong to, so we use a numeric keyed array, but we must keep tract of which index is who in that array because we are gradually filling it 
    $nameMapping = [];
    $indexCounter = 0;
    
    $maxValue = null;
    $minValue = null;
    
    foreach($data as $dataElm) 
    {
        $index = -1;
        if(array_key_exists($dataElm["id"], $nameMapping))
        {
            $index = (int) $nameMapping[$dataElm["id"]];
        }else{
            $index = (int) $indexCounter++;
            //var_dump($nameMapping);
            $data_out["debug"][] = "Increased counter to $index";
            $nameMapping[$dataElm["id"]] = (int) $index;
        }
        
        if($index !== -1)
            foreach($allowed_channels as $currentChannel)
            {
                if(array_key_exists($currentChannel, $dataElm)){
                    if(empty($data_out["data"][$currentChannel][$index])){
                        $data_out["data"][$currentChannel][$index] = $dataElmTmpl; //should only copy
                    }
                    $data_out["data"][$currentChannel][$index]["label"] = "Famille ".$dataElm["lname"]." (Kgs/P)";
                    $data_out["data"][$currentChannel][$index]["data"][(int) $month_remap[$dataElm["month"]]] = (float) $dataElm[$currentChannel];
                    if($maxValue < (float) $dataElm[$currentChannel] || $maxValue === null){
                        $maxValue = (float) $dataElm[$currentChannel];
                    }
                    if($minValue > (float) $dataElm[$currentChannel] || $minValue === null){
                        $minValue = (float) $dataElm[$currentChannel];
                    }
                    
                }
            
            }
    
    }
    
    //TODO: refactor this into a function you dumbass : the previous block is exactly the same !
    foreach($data2 as $dataElm) 
    {
        $index = -1;
        if(array_key_exists("**AVERAGE**", $nameMapping))
        {
            $index = (int) $nameMapping["**AVERAGE**"];
        }else{
            $index = (int) $indexCounter++;
            //var_dump($nameMapping);
            $data_out["debug"][] = "Increased counter to $index";
            $nameMapping["**AVERAGE**"] = (int) $index;
        }
        
        if($index !== -1)
            foreach($allowed_channels as $currentChannel)
            {
                if(array_key_exists($currentChannel, $dataElm)){
                    if(empty($data_out["data"][$currentChannel][$index])){
                        $data_out["data"][$currentChannel][$index] = $dataElmTmpl; //should only copy
                    }
                    $data_out["data"][$currentChannel][$index]["label"] = "Moyenne $currentChannel (Kgs/P)";
                    $data_out["data"][$currentChannel][$index]["data"][(int) $month_remap[$dataElm["month"]]] = (float) $dataElm[$currentChannel];
                    if($maxValue < (float) $dataElm[$currentChannel] || $maxValue === null){
                        $maxValue = (float) $dataElm[$currentChannel];
                    }
                    if($minValue > (float) $dataElm[$currentChannel] || $minValue === null){
                        $minValue = (float) $dataElm[$currentChannel];
                    }
                }
            
            }
    
    }
    
    $data_out["yScaleTicks"]["min"] = $minValue;
    $data_out["yScaleTicks"]["max"] = $maxValue;
    /*
    foreach($nameMapping as $key => $value)
    {
        $data_out["debug"][] = "Map $key => $value";
    }
    */
    /*
    foreach($allowed_channels as $currentChannel)
    {
        
        
        
        foreach($data as $entry){ //TODO we are not getting the index of $data_out["data"] used for the user
            
            
            $index = -1;
            if(array_key_exists($entry["id"], $nameMapping))
            {
                $index = (int) $nameMapping[$entry["id"]];
            }else{
                $index = (int) $indexCounter++;
                array_push($nameMapping, [ $entry["id"] => $index ]);
            }
            if(empty($data_out["data"][$index]))
            {
                $data_out["data"][$index] = array_clone($dataElm); //Init with default value
                $data_out["data"][$index]["label"] = "Famille ".$entry["lname"]." (Kgs/P)";
                
            }else{
                $data_out["data"][$index] = $data_out["data"][$index]; //TODO wtf is this
            }
            
            if(array_key_exists($currentChannel, $entry))
                $data_out["data"][$index]["data"][$month_remap[$entry["month"]]] = $entry[$currentChannel];
            
            $data_out["data"][$index]["channel"] = $currentChannel;
        }
         foreach($data2 as $entry){
            if(!array_key_exists($currentChannel, $entry)) { //FIXME i think all channels are returned inside array, thats why we have 
                continue;
            }
            //################################
            //$avgElm = array_clone($dataElm);  //Init with default value
            $index = -1;
            if(array_key_exists("**AVERAGE**", $nameMapping))
            {
                    $index = (int) $nameMapping["**AVERAGE**"];
            }else{
                    $index = (int) $indexCounter++;
                    array_push($nameMapping, [ "**AVERAGE**" => $index ]);
            }
            
            if(empty( $data_out["data"][$index])){
                $data_out["data"][$index] = array_clone($dataElm);
            }
            

            
            $data_out["data"][$index]["data"][$month_remap[$entry["month"]]] = $entry[$currentChannel];
                //$data_out["data"][$currentChannel][$index] = $dataElm;
            
                
            $data_out["data"][$index]["label"] = "Moyenne $currentChannel (Kgs/P)";
            //foreach($data2 as $avgEntry){
           // if(array_key_exists($currentChannel, $avgEntry))
                $data_out["data"][$index]["data"][$month_remap[$entry["month"]]] = $entry[$currentChannel];
            //}
            $data_out["data"][$index]["channel"] = $currentChannel;
        }
 
            
        //var_dump($nameMapping);
    }
    */
    
    /*
    foreach($data as $entry){ //FIXME: I fucked up this not how the output format works
        $currentChannel = "";
        foreach($entry as $key => $value){
            
            if(substr($key,0,2) === "d_")
            {
                $currentChannel = str_replace("d_", "", $key);
            }
        }
        if(empty($nameMapping[$entry["id"]])){
            $nameMapping[] = $entry["id"];
        }
        $dataChannels_memberIndex = array_search($entry["id"]);

        
            
        //$entry[""]
        if(empty($data_out["data"][$currentChannel][$dataChannels_memberIndex])){ //If this entry has already been done
        
            $currentEntryOut = $dataElm;
            $dataElm["label"] = $entry["lname"]." (Kgs/Personne)";
            $dataElm["data"] = $entry["lname"]." (Kgs/Personne)";
        
            $data_out["data"][$currentChannel][$dataChannels_memberIndex] =
        }
        
         = array_merge_recursive($currentEntryOut, $data_out["data"][$currentChannel][$dataChannels_memberIndex]);
        
    }
    */
    
    
}

echo json_encode($data_out);
?>
