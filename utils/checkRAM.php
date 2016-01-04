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


register_shutdown_function('shutdown');
set_error_handler("handlerError");

// $storeInRAM = RAM::getInstance()->flush();

$teamId = 80384650;
/*
$storeInRAM = RAM::getInstance()->getObjectsForTeam($teamId , RAM::RAM_TYPE_SPONSOR);
Utils::forDebug($storeInRAM);
*/
$storeInRAM = RAM::getInstance()->getObjectsForTeam($teamId , RAM::RAM_TYPE_FOOTBALLER);
// Utils::forDebug($storeInRAM);
 print_r($storeInRAM);
/*
$storeInRAM = RAM::getInstance()->getObjectsForTeam($teamId , RAM::RAM_TYPE_FOOTBALLER_FRIEND);
Utils::forDebug($storeInRAM);

$storeInRAM = RAM::getInstance()->getTeamById($teamId);
Utils::forDebug($storeInRAM);
*/

?>