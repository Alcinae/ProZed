<?php
require_once("conf/config.php");
require_once("includes/commons.php");
require_once("vendor/php/autoload.php");
require_once("includes/sys_account.php");
session_start();

    
    function pickUniqueColor(array $usedColors, $distance){ //TODO: prevent infinite loop by automatically reducing distance
        $color;
                        
        $uniqueColor = true;
        do{
            $color = new Color(true); //random Color 
            foreach($usedColors as $currentColor)
            {
                $uniqueColor &= ($color->getDistanceRgbFrom($currentColor) >= $distance);
            }
            
            if($distance>5)
                $distance -= 5;
                
        }while(!$uniqueColor);
        return $color;
    }
    
    $dataElmTmpl = [
        "label" => "PERSONNE (Kgs/Pers)",

        "lineTension" => 0.3,

        "backgroundColor" => "rgba(R,G,B,A)",

        "borderColor" => "rgba(R,G,B,A)",

        "pointRadius" => 5,

        "pointBackgroundColor" => "rgba(R,G,B,A)",

        "pointBorderColor" => "rgba(R,G,B,A)",

        //"pointHoverRadius" => 5,

        //"pointHoverBackgroundColor" => "rgba(R,G,B,A)",

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
$data_out = [];

if(!$_SESSION["user"]->hasCap("member"))
{
    $data_out["errorStr"] = "Acces refuse.";
}

$db = getDB();


$allowed_channels = ["tri", "verre", "ordure", "compost"];

//$_POST["channel"] = ["tri"];
//$_POST["from"] = [6];

//foreach($_POST as $elm){
 //   array_push($data_out, $elm);
//}

//TODO remove once testing done
//$_POST["channel"] = ["tri", "verre", "ordure", "compost"];
//$_POST["from"] = [6];

if(isset($_POST["channel"]) && isset($_POST["from"]))
{
    //TODO validate "from" and/or "channel"
    $channel;
    if(is_array($_POST["channel"]))
    {
        $channel = $_POST["channel"];
    }else{
        $channel = [$_POST["channel"]];
    }
    $subjects;
    if(is_array($_POST["from"]))
    {
        $subjects = $_POST["from"];
    }else{
        $subjects = [$_POST["from"]];
    }
    
    if(in_array("*ALL*", $channel))
    {
        $channel = $allowed_channels;
    }
    
    $data_out["debug"][] = "+SubjectNbr: ".count($subjects);
    
    foreach($subjects as $key)
    {
        $data_out["debug"][] = "+Subject: ".$key;
    }
    
    $shouldComputeAverages = false;
    
    if(in_array("*AVERAGE*", $subjects))
    {
        unset($subjects[array_search("*AVERAGE*", $subjects)]);
        $shouldComputeAverages = true;
    }
    
    $qMarks_subjects = str_repeat('?,', count($subjects) - 1) . '?';

    $channelColumns = "";
    $avgColumns = "";

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
    
        foreach($subjects as $key)
        {
            $data_out["debug"][] = "+SubjectAfter: ".$key;
        }
    
    
    if($errorOffset == count($channel)){
        foreach($channel as $key)
        {
            $data_out["debug"][] = "+Channel: ".$key;
        }
        $data_out["errorStr"] = "Channels invalides.";
    }else{
    
        
        
        $sumQuery = "SELECT members.id, members.lname, EXTRACT(MONTH FROM challenge.date) as month, ${channelColumns} FROM challenge INNER JOIN members ON challenge.member=members.id WHERE members.id IN (${qMarks_subjects}) GROUP BY members.id, EXTRACT(MONTH FROM challenge.date);";
        
        $querySum = $db->prepare($sumQuery);
        
        $queryAverage;
        if($shouldComputeAverages){
            $averageQuery = "SELECT month, ${avgColumns} FROM ( SELECT members.id, members.lname, challenge.member, EXTRACT(MONTH FROM challenge.date) as month, ${channelColumns} FROM challenge INNER JOIN members ON challenge.member=members.id GROUP BY members.id, EXTRACT(MONTH FROM challenge.date) ) vals INNER JOIN members m ON vals.member=m.id GROUP BY month; ";
            $queryAverage = $db->query($averageQuery);
        }
    

        $querySum->execute($subjects);
        
        
        $data = $querySum->fetchAll();
        
        $data2;
        if($shouldComputeAverages)
            $data2 = $queryAverage->fetchAll();
        
    
        //var_dump($data);
        //var_dump($data2);

        if(empty($data_out)) //might returns false if debug data is already in the array for example
            $data_out = $dataFrameTmpl;
        else
            $data_out = array_merge($data_out, $dataFrameTmpl);
            
        //when the js display the json data, it does not care to whom the data belong to, so we use a numeric keyed array, but we must keep tract of which index is who in that array because we are gradually filling it 
        $nameMapping = [];
        $indexCounter = 0;
        
        $maxValue = null;
        $minValue = null;
        
        $pickedColor = [];
        
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
                        if(empty($data_out["data"][$currentChannel][$index])){ //if this is a new item in the array
                            $data_out["data"][$currentChannel][$index] = $dataElmTmpl; //should only copy
                            $baseColor = pickUniqueColor($pickedColor, 20);
                            $pickedColor[] = $baseColor;
                            
                            //$baseColor = new Color(true);
                            $bgColor = new Color();
                            $bgColor->copyFrom($baseColor);
                            $bgColor->shade(0.6);
                            
                            $bgColorRgb = $bgColor->toRgbInt();
                            $lineColorRgb = $baseColor->toRgbInt();
                            
                            $data_out["data"][$currentChannel][$index]["backgroundColor"] = "rgba({$bgColorRgb["red"]},{$bgColorRgb["green"]},{$bgColorRgb["blue"]},0.05)";
                            $data_out["data"][$currentChannel][$index]["borderColor"] = "rgba({$lineColorRgb["red"]},{$lineColorRgb["green"]},{$lineColorRgb["blue"]},1)";
                            $data_out["data"][$currentChannel][$index]["pointBackgroundColor"] = "rgba({$bgColorRgb["red"]},{$bgColorRgb["green"]},{$bgColorRgb["blue"]},0.05)";
                            $data_out["data"][$currentChannel][$index]["pointBorderColor"] = "rgba({$lineColorRgb["red"]},{$lineColorRgb["green"]},{$lineColorRgb["blue"]},1)";
                            
                            
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
        if($shouldComputeAverages){
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
                                
                                $baseColor = pickUniqueColor($pickedColor, 25);
                                $pickedColor[] = $baseColor;
                                
                                //$baseColor = new Color(true);
                                $bgColor = new Color();
                                $bgColor->copyFrom($baseColor);
                                $bgColor->shade(0.6);
                                
                                $bgColorRgb = $bgColor->toRgbInt();
                                $lineColorRgb = $baseColor->toRgbInt();
                                
                                $data_out["data"][$currentChannel][$index]["backgroundColor"] = "rgba({$bgColorRgb["red"]},{$bgColorRgb["green"]},{$bgColorRgb["blue"]},0.05)";
                                $data_out["data"][$currentChannel][$index]["borderColor"] = "rgba({$lineColorRgb["red"]},{$lineColorRgb["green"]},{$lineColorRgb["blue"]},1)";
                                $data_out["data"][$currentChannel][$index]["pointBackgroundColor"] = "rgba({$bgColorRgb["red"]},{$bgColorRgb["green"]},{$bgColorRgb["blue"]},0.05)";
                                $data_out["data"][$currentChannel][$index]["pointBorderColor"] = "rgba({$lineColorRgb["red"]},{$lineColorRgb["green"]},{$lineColorRgb["blue"]},1)";
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
        }
        
        $data_out["yScaleTicks"]["min"] = $minValue;
        $data_out["yScaleTicks"]["max"] = $maxValue;
    }
}else{
    $keys = array_keys($_POST);
    foreach($keys as $key)
    {
        $data_out["debug"][] = "POSTKey: ".$key;
    }
    $data_out["debug"][] = "POSTKeyCount: ".count($_POST); 
    $data_out["errorStr"] = "Erreur de parametres.";
}

echo json_encode($data_out);
?>
