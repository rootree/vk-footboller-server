<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 18:00:40
 */

define("SECRET_KEY", "FUZ");

define("COMMAND_PING", "ping");
define("COMMAND_WELCOME", "welcome");
define("COMMAND_SAVE_PROFILE", "save_profile");
define("COMMAND_SAVE_SPONSORS", "save_sponsors");
define("COMMAND_BUY_FOOTBALLER", "buy_footballer");
define("COMMAND_SAVE_TEAM", "save_footballer");
define("COMMAND_GET_EMENY", "get_enemy");
define("COMMAND_GET_MATCH_RESULT", "get_result");
define("COMMAND_FRIEND_INFO", "friend_info");
define("COMMAND_FRIEND_TEAM", "friend_team");
define("COMMAND_DROP_ITEM", "drop_item");
define("COMMAND_BANK", "wonna_money");
define("COMMAND_CUZTOM", "customization");
define("COMMAND_FRIEND_IN_TEAM", "in_team_please");
define("COMMAND_UPDATE_ENERGY", "update_energy");
define("COMMAND_SEND_GIFT", "send_gift"); 
define("COMMAND_SET_AS_STAR", "set_as_star"); 
define("COMMAND_BUY_STADIUM", "buy_stadium"); 
define("COMMAND_BUY_STUDY_POINTS", "buy_study_points"); 
define("COMMAND_FRESH_ENERGY", "fresh_energy"); 
define("COMMAND_GET_GROUPS", "tour_groups"); 
define("COMMAND_GET_TEAM_INFO", "team_info"); 

define("COMMAND_SYSTEM", "system_command");


define("TYPE_FOOTBALLER_GOALKEEPER_CODE", "4");
define("TYPE_FOOTBALLER_FORWARD_CODE", "1");
define("TYPE_FOOTBALLER_SAFER_CODE", "3");
define("TYPE_FOOTBALLER_HALFSAFER_CODE", "2");
define("TYPE_FOOTBALLER_TEAMLEAD_CODE", "5");

define("TYPE_FOOTBALLER_GOALKEEPER", "goalkeeper");
define("TYPE_FOOTBALLER_FORWARD", "forward");
define("TYPE_FOOTBALLER_SAFER", "safe");
define("TYPE_FOOTBALLER_HALFSAFER", "halfsafe");
define("TYPE_FOOTBALLER_TEAMLEAD", "teamlead");

define("TOUR_TYPE_VK", 1);
define("TOUR_TYPE_COUNTRY", 2);
define("TOUR_TYPE_CITY", 3);
define("TOUR_TYPE_UNI", 4);

define("PRIZE_MODE_DISABLED", 0);
define("PRIZE_MODE_ACTIVATED", 99);
define("PRIZE_MODE_USED", 98);

define("TOUR_NOTIFY_START", 2);
define("TOUR_NOTIFY_NEW", 3);
define("TOUR_NOTIFY_NEW_NOTIFIED", 1);
define("TOUR_NOTIFY_START_NOTIFIED", 0);


define("MONEY_TYPE_REAL", "0");
define("MONEY_TYPE_GAME", "1");
define("EXCHANGE_RATE_REAL", "10");
define("EXCHANGE_RATE_GAME", "3000");
 
define("VK_MAILING_SPEED", '440'); // �������� �������� � ���.���
define("PERFOMANCE_LOG", false); // �������� �������� � ���.���

define('NOTIFY_STATUS_NEW',      '1');
define('NOTIFY_STATUS_STARTED',  '2');
define('NOTIFY_STATUS_ENTED',    '3');
define('NOTIFY_STATUS_CANCELED', '4');

define('VK_APPLICATION_STATUS', 'и мой ФК «%s»');

define("NEWS_LIMIT", 10);

define("TOUR_TIMER_FILE", "tourTimer.json");

define("MAX_TICK_TIME", 5000); // В микросекундах
/*
        'db'            => 'crazy_statistic',
        'table'         => 'crazy_test',
        'field'         => 'user_VK_id',ELECT vk_id, energy FROM teams
*/


 
?>