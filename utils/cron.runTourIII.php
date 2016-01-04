<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 19:09:48
 */




$runningOn = 2;
switch ($runningOn){
    case 1: // Server
        define("SYSTEM_PATH", "/var/server/");
        break;
    case 2: // Home
        define("SYSTEM_PATH", "..");
        break;
}

include_once(SYSTEM_PATH . "/System/settings.php");
include_once(SYSTEM_PATH . "/System/function.php");

$startString = "Running Tour III ... ";
echo $startString . date("[Y-m-d H:i:s.m]") . PHP_EOL ;

register_shutdown_function('shutdown');
set_error_handler("handlerError");

$actionResult = null;
$scoreStore = array();

if(SQL::getInstance()->connect_error){
    $actionResult = new ErrorPoint(ErrorPoint::CODE_SQL, "Соединение провалено (" . SQL::getInstance()->connect_error . ")", ErrorPoint::TYPE_SYSTEM);
    die("No connection");
}


/**
 * Обновляем места среди страны
 */
/*
$sql_template = "UPDATE tour_groups SET was_closed = 1 ;";
$SQLResultTeams = SQL::getInstance()->query($sql_template);
if($SQLResultTeams instanceof ErrorPoint){
    return $SQLResultTeams;
}
*/
$sql_template = "DELETE FROM tour_groups;";
$SQLResultTeams = SQL::getInstance()->query($sql_template);
if($SQLResultTeams instanceof ErrorPoint){
    return $SQLResultTeams;
}

$sql_template = "DELETE FROM tour_groups_details;";
$SQLResultTeams = SQL::getInstance()->query($sql_template);
if($SQLResultTeams instanceof ErrorPoint){
    return $SQLResultTeams;
}


createTourGroups(TOUR_TYPE_VK, 0);

createTourGroupsByPlace(TOUR_TYPE_COUNTRY);
createTourGroupsByPlace(TOUR_TYPE_CITY);
createTourGroupsByPlace(TOUR_TYPE_UNI);
 
echo str_repeat(" ", strlen($startString)) . date("[Y-m-d H:i:s.m]") . " Finished " . PHP_EOL;

switch ($runningOn){
    case 1: // Server
        system("php -f " . SYSTEM_PATH . "/utils/cron.playOffTourIII.php >> " . SYSTEM_PATH . "/_logs/cron.playOffTourIII.log");
        break;
    case 2: // Home
        include (SYSTEM_PATH . "/utils/cron.playOffTourIII.php");
        break;
}



?>