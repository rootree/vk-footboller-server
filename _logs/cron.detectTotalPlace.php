<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 19:09:48
 */




$runningOn = 1;
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

$startString = "Running Detecting total place III ... ";
echo $startString . date("[Y-m-d H:i:s.m]") . PHP_EOL ;

register_shutdown_function('shutdown');
set_error_handler("handlerError");

$actionResult = null;
$scoreStore = array();

if(SQL::getInstance()->connect_error){
    $actionResult = new ErrorPoint(ErrorPoint::CODE_SQL, "Соединение провалено (" . SQL::getInstance()->connect_error . ")", ErrorPoint::TYPE_SYSTEM);
    die("No connection");
}


$sql =
"SELECT 
    teams.vk_id
FROM teams
order by teams.level desc, teams.counter_won desc ";
 
$SQLresult = SQL::getInstance()->query($sql);

if($SQLresult instanceof ErrorPoint){
    return $SQLresult;
}

echo "Need to upgrade " . $SQLresult->num_rows . " country tours"  . PHP_EOL ;

if($SQLresult->num_rows){

    $counterPlace = 1;
    $sql = "";
    while ($teamObject = $SQLresult->fetch_object()){

/*        if($counterPlace % 100 == 0){
            SQL::getInstance()->query($sql); 
            $sql = "";
        }*/

        $sql_template = "UPDATE teams SET total_place = %d WHERE teams.vk_id = (%s);";
        $sql = sprintf($sql_template,
            $counterPlace,
            $teamObject->vk_id 
        );
		
		SQL::getInstance()->query($sql); 
		usleep(500);
        $counterPlace ++;
    }
}

print(" stop..." . date("[Y-m-d H:i:s.m]") . '<br/>' . PHP_EOL);
 
?>