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

echo "Running placer updater ... " . date("[Y-m-d H:i:s.m]") . PHP_EOL ;

register_shutdown_function('shutdown');
set_error_handler("handlerError");


$userId =   $GLOBALS['argv'][1];
$authKey =  $GLOBALS['argv'][2];


$SYSTEM_COMMAND = 'ping'; 
$SECRET_KEY = 'FUZ';


$ch = curl_init();

curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLINFO_HEADER_OUT, 0);
curl_setopt($ch, CURLOPT_SSLVERSION, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);


$serverCheckSum = md5($authKey . $SECRET_KEY . $userId);

$postLogIn = array(
    'checksum='.     $serverCheckSum,
    'referrerId='.   0,
    'id='.           $userId,
    'authKey='.      $authKey,
    'command='.      $SYSTEM_COMMAND,
    'params='.       "{}",
);


$postLogIn = implode('&', $postLogIn);

$url='http://188.93.17.159:8080/server/index.php' ;
$Referer = 'http://www.webnizer.com/';

curl_setopt($ch, CURLOPT_REFERER, $Referer);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postLogIn);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_URL, $url);

track_stats(); // Отслеживаем производительность

$serverResponse = curl_exec($ch);

track_stats(); // Отслеживаем производительность

if(strpos ( $serverResponse, '"response":{"isInstalled":1') === false){
    Utils::forDebug($serverResponse);
}

echo str_repeat(" ", 27) . date("[Y-m-d H:i:s.m]") . " Finished " . PHP_EOL;


?>