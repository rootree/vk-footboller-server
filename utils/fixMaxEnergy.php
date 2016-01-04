<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 19:09:48
 */

echo "Running energy FIX updater ... " . date("[Y-m-d H:i:s.m]") . PHP_EOL ;

$runningOn = 2;
switch ($runningOn){
    case 1: // Server
        define("SYSTEM_PATH", "/home/GameServer");
        break;
    case 2: // Home
        //define("SYSTEM_PATH", "..");
        define("SYSTEM_PATH", "C:/srv/footboll/server");
        break;
}

include_once(SYSTEM_PATH . "/System/settings.php");
include_once(SYSTEM_PATH . "/System/function.php");

register_shutdown_function('shutdown');
set_error_handler("handlerError");

$actionResult = null;

if(SQL::getInstance()->connect_error){
    $actionResult = new ErrorPoint(ErrorPoint::CODE_SQL, "Соединение провалено (" . SQL::getInstance()->connect_error . ")", ErrorPoint::TYPE_SYSTEM);
    die("No connection");
}


$sql = "UPDATE teams set level = " . GlobalParameters::START_LEVEL . ", money = " . GlobalParameters::START_MONEY .
        ", money_real = " . GlobalParameters::START_REAL_MONEY . " "
        . " where level = 0 " ;
SQL::getInstance()->query($sql);
echo "Fixed users: " . SQL::getInstance()->affected_rows . " " . PHP_EOL;
 

$sql_template = "SELECT teams.level, teams.vk_id, (
SELECT EXP(SUM(LOG(item_sponsors.energy))) FROM sponsors
JOIN item_sponsors ON item_sponsors.id = sponsors.sponsor_id
WHERE teams.vk_id = sponsors.vk_id
GROUP BY sponsors.vk_id
) AS sponsorIndex FROM teams WHERE teams.energy_max = 0";

$sql = sprintf($sql_template);

$fixDBResult = SQL::getInstance()->query($sql);

if($fixDBResult->num_rows){

    while ($team = $fixDBResult->fetch_object()){
		if(empty($team->sponsorIndex)){
			$team->sponsorIndex = 1;
		}
		$max = floor(floatval($team->sponsorIndex) * LevelsGrid::getInstance()->getBaseEnergy($team->level));
 
        $sql = "UPDATE teams set energy_max = " . $max
                . " where vk_id = " . $team->vk_id;
        SQL::getInstance()->query($sql);

    }
}

echo str_repeat(" ", 27) . date("[Y-m-d H:i:s.m]") . ", updated users: " . $fixDBResult->num_rows . " " . PHP_EOL;

?>