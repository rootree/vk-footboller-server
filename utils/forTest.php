<?php
/** 
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 07.07.2010
 * Time: 17:55:57 
 */


$runningOn = 2;
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

$startString = "Closing Tour III ... ";
echo $startString . date("[Y-m-d H:i:s.m]") . PHP_EOL ;

register_shutdown_function('shutdown');
set_error_handler("handlerError");

$actionResult = null;
$scoreStore = array();

if(SQL::getInstance()->connect_error){
    $actionResult = new ErrorPoint(ErrorPoint::CODE_SQL, "—оединение провалено (" . SQL::getInstance()->connect_error . ")", ErrorPoint::TYPE_SYSTEM);
    die("No connection");
}



// TODO: надо дописать установку дат
// » раздачу конечный коофициента акции
// пометить дл€ учавствующих игроков что дл€ них закончены соревновани€






//////////////////// ќбновл€ем всех в пам€ти //////////////////

$sql_template = "SELECT vk_id, tour_place_vk, tour_place_country, tour_place_city, tour_place_uni FROM teams
WHERE ( tour_place_vk > 0 or tour_place_country > 0 or tour_place_city > 0 or tour_place_uni > 0 ) AND date_reg IS NOT NULL and  vk_id = 100206819;";
$sql = $sql_template;

$SQLresultSeelctTeam = SQL::getInstance()->query($sql);
if($SQLresultSeelctTeam instanceof ErrorPoint){
    return $SQLresultSeelctTeam;
}

echo "Upgrading for All users, count records: " . $SQLresultSeelctTeam->num_rows . " teams"  . PHP_EOL ;

if($SQLresultSeelctTeam->num_rows){

    while ($teamObject = $SQLresultSeelctTeam->fetch_object()){

        $bonus = 1;
        $bonus *= GoldCointsGrid::getInstance()->getBonusByPlace(TOUR_TYPE_VK, $teamObject->tour_place_vk);
        $bonus *= GoldCointsGrid::getInstance()->getBonusByPlace(TOUR_TYPE_COUNTRY, $teamObject->tour_place_country);
        $bonus *= GoldCointsGrid::getInstance()->getBonusByPlace(TOUR_TYPE_CITY, $teamObject->tour_place_city);
        $bonus *= GoldCointsGrid::getInstance()->getBonusByPlace(TOUR_TYPE_UNI, $teamObject->tour_place_uni);

        if($bonus > GlobalParameters::MAX_TOUR_BONUS){
            $bonus = GlobalParameters::MAX_TOUR_BONUS;
        }

        $sql_template = "UPDATE teams set tour_bonus = %f, tour_bonus_time = 0;";
        $sql = sprintf($sql_template,
            round($bonus, 2)
        );
        $SQLResultTeamsFake = SQL::getInstance()->query($sql); echo $bonus; exit();

  /*      $teamId = $teamObject->vk_id; echo $teamId . "\n";
        $team = RAM::getInstance()->getTeamById($teamId);
        if(!empty($team)){
            RAM::getInstance()->changeTeamField($teamId, 'tourBonus', $bonus);
            RAM::getInstance()->changeTeamField($teamId, 'tourBonusTime', 0);
        }*/
    }
}
 
?>