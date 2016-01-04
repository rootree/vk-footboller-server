<?php

ob_start();

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

register_shutdown_function('shutdown');
set_error_handler("handlerError");

$userId       = intval($_GET["userId"]);
$authKey      = @trim($_GET["authKey"]);
$checkSum     = @trim($_GET["checkSum"]);
$command      = @trim($_GET["command"]);

$serverCheckSum = md5($authKey . SECRET_KEY . $userId);
$actionResult = NULL;

if($serverCheckSum != $checkSum){
    $actionResult = new ErrorPoint(ErrorPoint::CODE_BAD_MD5, "WFT", ErrorPoint::TYPE_USER);
}

if(!in_array(getRealIP(), $allowIPForSystemCommand)){
    $actionResult = new ErrorPoint(ErrorPoint::CODE_BAD_MD5, "WFT!: " . getRealIP(), ErrorPoint::TYPE_USER);
}

if(SQL::getInstance()->connect_error){
    $actionResult = new ErrorPoint(ErrorPoint::CODE_SQL, "Невозможно подключиться (" . SQL::getInstance()->connect_error . ")", ErrorPoint::TYPE_SYSTEM);
}

if(empty($checkSum) || empty($authKey) || empty($command)){
    $actionResult = new ErrorPoint(ErrorPoint::CODE_PARAMETERS, "Не полный запрос", ErrorPoint::TYPE_USER);
}


if($actionResult instanceof ErrorPoint){
}else{
    $response = array();
    switch($command){
        case "COMAND_ADD":
 
            $message = trim($_GET['message']);
            if(empty($message)){
                $actionResult = new ErrorPoint(ErrorPoint::CODE_PARAMETERS, "Не получен текст сообщения", ErrorPoint::TYPE_USER);
                break;
            }

            $sql_template =
"INSERT INTO notify (
    notify_message,
    notify_status, 
    project_id,
    notify_date

) VALUES (
    '%s',
    %d,
    %d,
    NOW()
)";

            $sql = sprintf($sql_template,
                SQL::getInstance()->real_escape_string(trim($_GET['message'])),
                NOTIFY_STATUS_NEW,
                1
            );

            $actionResult = SQL::getInstance()->query($sql);
            $response['notify_id'] = SQL::getInstance()->getInsertedId();

            break;
        case "COMAND_GET":

            break;
        case "COMAND_CHECK":

            break;
    }

    if(!($actionResult instanceof ErrorPoint)){
        echo json_encode($response);
    }

}

ob_end_flush();

?> 