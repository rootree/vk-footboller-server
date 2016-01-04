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
        define("SYSTEM_PATH", ".");
        break;
}

include_once(SYSTEM_PATH . "/System/settings.php");
include_once(SYSTEM_PATH . "/System/function.php");

echo "Running energy updater ... " . date("[Y-m-d H:i:s.m]") . PHP_EOL ;

register_shutdown_function('shutdown');
set_error_handler("handlerError");

$actionResult = null;

if(SQL::getInstance()->connect_error){
    $actionResult = new ErrorPoint(ErrorPoint::CODE_SQL, "Соединение провалено (" . SQL::getInstance()->connect_error . ")", ErrorPoint::TYPE_SYSTEM);
    die("No connection");
}


$sql_template =
        "UPDATE teams SET teams.energy = teams.energy + " . GlobalParameters::ENERGY_PER_MATCH . " WHERE teams.energy_max > teams.energy";

$sql = sprintf($sql_template,
               CRON_UPDATE_ENERGY_RATE
);

SQL::getInstance()->query($sql);

$sql_template =
        "UPDATE teams SET teams.energy = teams.energy_max WHERE teams.energy_max < teams.energy";

$sql =($sql_template);

SQL::getInstance()->query($sql);


//////////////////// Обновляем всех в памяти //////////////////

if(RAM::getInstance()->getIsServerConnected()){


    $sql_template = "SELECT vk_id, energy FROM teams ;";
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
                RAM::getInstance()->changeTeamField($teamId, 'currentEnergy', $teamObject->energy);
            }
        }
    }

}


$energyTimer = filemtime(SYSTEM_LOGS . "/cron.updateEnergy.log"); // microtime
RAM::getInstance()->setEnergyLastUpdate($energyTimer);

echo str_repeat(" ", 27) . date("[Y-m-d H:i:s.m]") . ", updated users: " . SQL::getInstance()->affected_rows . " " . PHP_EOL;

?>