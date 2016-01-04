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

$serverCheckSum = md5($authKey . SECRET_KEY . $userId);

if($serverCheckSum != $checkSum){
    $actionResult = new ErrorPoint(ErrorPoint::CODE_BAD_MD5, "WFT", ErrorPoint::TYPE_USER);
}

if(!in_array(getRealIP(), $allowIPForSystemCommand)){
    $actionResult = new ErrorPoint(ErrorPoint::CODE_BAD_MD5, "WFT!: " . getRealIP(), ErrorPoint::TYPE_USER);
}

UserParameters::setUserId($userId);

if(SQL::getInstance()->connect_error){
    $actionResult = new ErrorPoint(ErrorPoint::CODE_SQL, "Невозможно подключиться (" . SQL::getInstance()->connect_error . ")", ErrorPoint::TYPE_SYSTEM);
    break;
}

echo
'<?xml version="1.0"?>
<userInfo>';

if(!array_key_exists("onlyProfile", $_GET)){
	echo '<requests>';
	getUsersActivity();
	echo "</requests>";
} 

$team = new Team();
$team->initById(UserParameters::getUserId());
 
echo "<team><![CDATA[" . json_encode(JSONPrepare::team($team)) . "]]></team>";
 
echo "</userInfo>"; 
ob_end_flush();
 
?> 