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

$startString = "Closing Tour III ... ";
echo $startString . date("[Y-m-d H:i:s.m]") . PHP_EOL ;

register_shutdown_function('shutdown');
set_error_handler("handlerError");

$actionResult = null;
$scoreStore = array();

if(SQL::getInstance()->connect_error){
    $actionResult = new ErrorPoint(ErrorPoint::CODE_SQL, "Соединение провалено (" . SQL::getInstance()->connect_error . ")", ErrorPoint::TYPE_SYSTEM);
    die("No connection");
}



// TODO: надо дописать установку дат
// И раздачу конечный коофициента акции
// пометить для учавствующих игроков что для них закончены соревнования






//////////////////// Обновляем всех в памяти //////////////////

$sql_template = "SELECT vk_id, user_name, tour_place_vk, tour_place_country, tour_place_city, tour_place_uni FROM teams
WHERE ( tour_place_vk > 0 or tour_place_country > 0 or tour_place_city > 0 or tour_place_uni > 0 ) AND date_reg IS NOT NULL ;";
$sql = $sql_template;

$SQLresultSeelctTeam = SQL::getInstance()->query($sql);
if($SQLresultSeelctTeam instanceof ErrorPoint){
    return $SQLresultSeelctTeam;
}

echo "Upgrading for All users, count records: " . $SQLresultSeelctTeam->num_rows . " teams"  . PHP_EOL ;

if($SQLresultSeelctTeam->num_rows){

    $api = new VKapi(VK_API_SECRET, VK_API_ID, VK_MAILING_SPEED);
    
    while ($teamObject = $SQLresultSeelctTeam->fetch_object()){
 
        $bonus = 1;
        $bonus *= GoldCointsGrid::getInstance()->getBonusByPlace(TOUR_TYPE_VK, $teamObject->tour_place_vk);
        $bonus *= GoldCointsGrid::getInstance()->getBonusByPlace(TOUR_TYPE_COUNTRY, $teamObject->tour_place_country);
        $bonus *= GoldCointsGrid::getInstance()->getBonusByPlace(TOUR_TYPE_CITY, $teamObject->tour_place_city);
        $bonus *= GoldCointsGrid::getInstance()->getBonusByPlace(TOUR_TYPE_UNI, $teamObject->tour_place_uni);

        if($bonus > GlobalParameters::MAX_TOUR_BONUS){
            $bonus = GlobalParameters::MAX_TOUR_BONUS;
        }
        $bonus = round($bonus, 2);
        $sql_template = "UPDATE teams set tour_bonus = %f, tour_bonus_time = 0 WHERE vk_id = %d;";
        $sql = sprintf($sql_template,
            $bonus, $teamObject->vk_id
        ); 
        $SQLResultTeamsFake = SQL::getInstance()->query($sql);



        $notify_message = $teamObject->user_name . ", ";

        if($teamObject->tour_place_vk){
            $notify_message .= "безподобные результаты! В чемпионате ВКонтакта Вы заняли " .$teamObject->tour_place_vk . "-е место!";
        }else{

            if($teamObject->tour_place_country){
                $notify_message .= "просто великолепно! В чемпионате страны Вы заняли " .$teamObject->tour_place_country . "-е место!";
            }else{

                if($teamObject->tour_place_city){
                    $notify_message .= "радостные новости! В чемпионате города Вы заняли " .$teamObject->tour_place_city . "-е место!";
                }else{
                    $notify_message .= "хорошие результаты! В чемпионате ВУЗа Вы заняли " .$teamObject->tour_place_uni . "-е место!";
                }
            }
        }
 
        $notify_message .= " Примите наши поздравления и скидку чемпиона в размере " . ( $bonus * 100 - 100) . "% на всех футболистов и тренеров";

        $notifyForUsers = array();
        $apiResult = NULL;


		$apiResult = $api->sendNotification($teamObject->vk_id, $notify_message);
		usleep(VK_MAILING_SPEED);
 
  /*      $teamId = $teamObject->vk_id; echo $teamId . "\n";
        $team = RAM::getInstance()->getTeamById($teamId);
        if(!empty($team)){
            RAM::getInstance()->changeTeamField($teamId, 'tourBonus', $bonus);
            RAM::getInstance()->changeTeamField($teamId, 'tourBonusTime', 0);
        }*/
    }
}



 



//////////////////// Это подготовка к новому //////////////////



TourSatellite::setTimerDate(time(), time() + (5 * 24 * 60 * 60), TOUR_NOTIFY_NEW);

$sql_template = "UPDATE teams SET tour_notify = " . TOUR_NOTIFY_NEW . ", tour_III = 0;"; // tour_III = 0
$SQLResultTeams = SQL::getInstance()->query($sql_template);
/*
//////////////////// Обновляем всех в памяти //////////////////

$sql_template = "SELECT vk_id FROM teams ;";
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
            RAM::getInstance()->changeTeamField($teamId, 'tourNotify', TOUR_NOTIFY_NEW_NOTIFIED);
      //      RAM::getInstance()->changeTeamField($teamId, 'tourIII', 0); TOUR_NOTIFY_NEW 
        }
    }
}


*/
echo str_repeat(" ", strlen($startString)) . date("[Y-m-d H:i:s.m]") . " Finished " . PHP_EOL;

RAM::getInstance()->flush();

system("php -f " . SYSTEM_PATH . "/utils/cron.updatePlaceInTour.php >> " . SYSTEM_PATH . "/_logs/cron.updatePlaceInTour.log");


?>