<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 19:09:48
 */




$runningOn = 1;
switch ($runningOn){
    case 1: // Server
        define("SYSTEM_PATH", "/var/server");
        break;
    case 2: // Home
        define("SYSTEM_PATH", "..");
        break;
}

include_once(SYSTEM_PATH . "/System/settings.php");
include_once(SYSTEM_PATH . "/System/function.php");

$startString = "Running Paly Off Tour III ... ";
echo $startString . date("[Y-m-d H:i:s.m]") . PHP_EOL ;

register_shutdown_function('shutdown');
set_error_handler("handlerError");

$actionResult = null;

if(SQL::getInstance()->connect_error){
    $actionResult = new ErrorPoint(ErrorPoint::CODE_SQL, "Соединение провалено (" . SQL::getInstance()->connect_error . ")", ErrorPoint::TYPE_SYSTEM);
    die("No connection");
}


/**
 * Обновляем места среди страны
 */
 
$sql_template = "DELETE FROM tour_play_off;";
$SQLResultTeams = SQL::getInstance()->query($sql_template);
if($SQLResultTeams instanceof ErrorPoint){
    return $SQLResultTeams;
}

/////////// TODO ////////////////////////////////////////////

$sql_template = "UPDATE teams SET tour_notify = " . TOUR_NOTIFY_START . ", tour_place_vk = 0, tour_place_country = 0, tour_place_city = 0, ".
        "tour_place_uni = 0, tour_bonus = 0, tour_bonus_time = 0;;";
$SQLResultTeams = SQL::getInstance()->query($sql_template);

//////////////////// Обновляем всех в памяти //////////////////

/*$sql_template = "SELECT vk_id FROM teams ;";
$sql = $sql_template;

$SQLresultSeelctTeam = SQL::getInstance()->query($sql);
if($SQLresultSeelctTeam instanceof ErrorPoint){
    return $SQLresultSeelctTeam;
}

echo "Upgrading for All users, count records: " . $SQLresultSeelctTeam->num_rows . " teams"  . PHP_EOL ;

if($SQLresultSeelctTeam->num_rows){

    while ($teamObject = $SQLresultSeelctTeam->fetch_object()){

        $teamId = $teamObject->vk_id;
        $team = RAM::getInstance()->getTeamById($teamId);
        if(!empty($team)){
            RAM::getInstance()->changeTeamField($teamId, 'tourNotify', TOUR_NOTIFY_START);

            RAM::getInstance()->changeTeamField($teamId, 'tourPlaceVK', 0);
            RAM::getInstance()->changeTeamField($teamId, 'tourPlaceCountry', 0);
            RAM::getInstance()->changeTeamField($teamId, 'tourPlaceCity', 0);
            RAM::getInstance()->changeTeamField($teamId, 'tourPlaceUniversity', 0);

            RAM::getInstance()->changeTeamField($teamId, 'tourBonus', 0);
            RAM::getInstance()->changeTeamField($teamId, 'tourBonusTime', 0); 
        }
    }
}*/








echo "Getting tours and place : "  . PHP_EOL ;

$sql_template = "SELECT tour_type, tour_placer_id FROM tour_groups WHERE was_closed = 0 GROUP BY tour_type, tour_placer_id;";
$SQLResultTeams = SQL::getInstance()->query($sql_template);

if($SQLResultTeams instanceof ErrorPoint){
    return $SQLResultTeams;
}

if($SQLResultTeams->num_rows){
    while ($res = $SQLResultTeams->fetch_object()){
        createPlayOff($res->tour_type, $res->tour_placer_id);
    }
}
 
TourSatellite::setTimerDate(time(), time() + (1 * 24 * 60 * 60), TOUR_NOTIFY_START);
//TourSatellite::setTimerDate(time(), time());

RAM::getInstance()->flush();

echo str_repeat(" ", strlen($startString)) . date("[Y-m-d H:i:s.m]") . " Finished " . PHP_EOL;
  
?>