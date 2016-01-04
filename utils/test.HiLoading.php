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

$sql_template = "SELECT vk_id, auth_key FROM teams WHERE auth_key != '' GROUP BY vk_id, auth_key ORDER BY RAND() LIMIT 1000 ;";
$sql = $sql_template;

$SQLresultSeelctTeam = SQL::getInstance()->query($sql);
if($SQLresultSeelctTeam instanceof ErrorPoint){
    return $SQLresultSeelctTeam;
}


if($SQLresultSeelctTeam->num_rows){

    while ($teamObject = $SQLresultSeelctTeam->fetch_object()){ 
        $cmd_command = 'php /var/server/utils/test.SendRequest.php' . " ". escapeshellarg($teamObject->vk_id) . " " . escapeshellarg($teamObject->auth_key)  ;
        pclose(popen($cmd_command, 'r'));;
        echo $cmd_command . "\n";
    }
}


echo str_repeat(" ", 27) . date("[Y-m-d H:i:s.m]") . ", updated users: " . SQL::getInstance()->affected_rows . " " . PHP_EOL;

?>