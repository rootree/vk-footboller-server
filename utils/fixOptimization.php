<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 19:09:48
 */

echo "Running energy FIX updater ... " . date("[Y-m-d H:i:s.m]") . PHP_EOL ;

$runningOn = 1;
switch ($runningOn){
    case 1: // Server
        define("SYSTEM_PATH", "/var/server");
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


 

$sql_template = "SELECT * FROM teams_fix  ";

$sql = sprintf($sql_template);

$fixDBResult = SQL::getInstance()->query($sql);

if($fixDBResult->num_rows){

    while ($team = $fixDBResult->fetch_object()){


        $sql = "UPDATE teams set
        experience = experience + " . $team->experience . ",
        money = money + " . $team->money . ",
        money_real = money_real + " . $team->money_real . ",
        stady_point = stady_point + " . $team->stady_point . ",
        counter_won = counter_won + " . $team->counter_won . ",
        counter_choose = counter_choose + " . $team->counter_choose . ",
        counter_lose = counter_lose + " . $team->counter_lose . ",
        counter_tie = counter_tie + " . $team->counter_tie . ",
        tour_III = tour_III + " . $team->tour_III . ",

        
        trainer_id =   " . $team->trainer_id . ",
        in_team =   " . $team->in_team . ",
        in_group =  " . $team->in_group . ",
        is_prized =  " . $team->is_prized . ",
        stadium_id =  " . $team->stadium_id . ",
        country =  " . $team->country . ",
        city =  " . $team->city . ",
        university =  " . $team->university . "
                  where vk_id = " . $team->vk_id;
         SQL::getInstance()->query($sql);



    }
}

echo str_repeat(" ", 27) . date("[Y-m-d H:i:s.m]") . ", updated users: " . $fixDBResult->num_rows . " " . PHP_EOL;

?>