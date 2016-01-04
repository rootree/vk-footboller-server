<?php

if(!defined("SYSTEM_PATH")){
    die("NOT ALLOW RUNNING .!..");
}

include_once(SYSTEM_PATH . "/System/constants.php");
include_once(SYSTEM_PATH . "/System/dependOnServer.php");

date_default_timezone_set('Europe/Moscow');

define("LOG_USER_ERROR", "userError.xml");
define("LOG_USER_DEBUG", "debuging.log");
define("LOG_PERFORMANCE", "performance.log");
define("LOG_USER_ACTION", "userAction.xml");
define("LOG_SYSTEM_ERROR", "systemError.xml");
define("LOG_FATAL_ERROR", "fatalError.log");
define("LOG_COMMAND_PATH", SYSTEM_LOGS . "/_users");

define("CRON_UPDATE_ENERGY_RATE", "10"); // � �����

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('log_errors', TRUE);
ini_set('error_log', SYSTEM_LOGS . '/' . LOG_FATAL_ERROR);
ini_set('html_errors', FALSE);
ini_set('error_prepend_string', '<phpfatalerror>');
ini_set('error_append_string', '</phpfatalerror>');


$allowCommands = array(
    COMMAND_PING,
    COMMAND_WELCOME,
    COMMAND_SAVE_PROFILE,
    COMMAND_SAVE_SPONSORS,
    COMMAND_BUY_FOOTBALLER,
    COMMAND_SAVE_TEAM,
    COMMAND_GET_EMENY,
    COMMAND_GET_MATCH_RESULT,
    COMMAND_FRIEND_INFO,
    COMMAND_FRIEND_TEAM,
    COMMAND_DROP_ITEM,
    COMMAND_SYSTEM,
    COMMAND_BANK,
    COMMAND_CUZTOM,
    COMMAND_FRIEND_IN_TEAM,
    COMMAND_UPDATE_ENERGY,
    COMMAND_SEND_GIFT,
    COMMAND_SET_AS_STAR,
    COMMAND_BUY_STADIUM,
    COMMAND_BUY_STUDY_POINTS,
    COMMAND_FRESH_ENERGY,
    COMMAND_GET_GROUPS,
    COMMAND_GET_TEAM_INFO,
);

$allowIPForSystemCommand = array(
    "70.40.192.88",
    "74.220.199.22",
    "127.0.0.1",
    "195.91.235.79",
);


?>