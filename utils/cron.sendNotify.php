<?php

/**
 * Отправка сообщение пользователям ВКонтакта
 */

$runningOn = 1;
switch ($runningOn){
    case 1: // Server
        define("SYSTEM_PATH", "/var/server");
        break;
    case 2: // Home
    case 4: // Home
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


$send = false;

$sql_template = "select * from notify where notify_status = %d order by notify_id desc limit 1;";

$sql = sprintf($sql_template,
    NOTIFY_STATUS_NEW
);

$SQLresult = SQL::getInstance()->query($sql);

if($SQLresult instanceof ErrorPoint){
    return $SQLresult;
}

if($SQLresult->num_rows){

    $usersCount = 0;

    while ($messageObject = $SQLresult->fetch_object()){

        $send = true;

        $sql_template = 'UPDATE notify SET notify_status = %d WHERE notify_id = ' .$messageObject->notify_id . ';';
        $sql = sprintf($sql_template,
            NOTIFY_STATUS_STARTED
        );

        $SQLresultTemp = SQL::getInstance()->query($sql);
        if($SQLresultTemp instanceof ErrorPoint){
            break;
        }

        $api = new VKapi(VK_API_SECRET, VK_API_ID, VK_MAILING_SPEED);

        $sql_template = "SELECT vk_id from teams;";
        $sql = $sql_template;

        $SQLresultTeam = SQL::getInstance()->query($sql);
        if($SQLresultTeam instanceof ErrorPoint){
            break;
        }

        $notifyForUsers = array();
        $apiResult = NULL;
/*
        $notifyForUsers = "100206819";
        $apiResult = $api->sendNotification($notifyForUsers, $messageObject->notify_message);

        Utils::forDebug($apiResult);
*/
        if($SQLresultTeam->num_rows){

            while ($teamObject = $SQLresultTeam->fetch_object()){

                $usersCount++;
                $notifyForUsers[] = $teamObject->vk_id;

                if(count($notifyForUsers) == 1000) {

                    $apiResult = $api->sendNotification($notifyForUsers, $messageObject->notify_message);
                    usleep(VK_MAILING_SPEED);
                    if($apiResult['error']){
                        print("Error message!!!: " . $apiResult['error']['error_msg']. ' !!!<br/>' . PHP_EOL);
                        $usersCount-=count($notifyForUsers);
                    } else {
                        print("progress delivered for ".$usersCount." users"  . '<br/>' . PHP_EOL);
                    }
                    $notifyForUsers = array(); 
                }
            }
        }

        $sql_template = 'UPDATE notify SET notify_status = %d WHERE notify_id = ' .$messageObject->notify_id . ';';
        $sql = sprintf($sql_template,
            NOTIFY_STATUS_ENTED
        );

        $SQLresultTemp = SQL::getInstance()->query($sql);
        if($SQLresultTemp instanceof ErrorPoint){
		 
			if(SQL::getInstance()->errno == 2013){
				SQL::getInstance(true)->query($sql);
			}
			
            break;
        }

    }
}

if(!$send) {
    print("distribution not found" . '<br/>' . PHP_EOL);
} else {
    print("success, delivered for ".$usersCount." users" . '<br/>' . PHP_EOL);
}

print("notifyer stop..." . date("[Y-m-d H:i:s.m]") . '<br/>' . PHP_EOL);

?>