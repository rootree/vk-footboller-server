<?php


// put your code here

/* 
 * не отдавать пользователю ненужной инфы. только то что ему нужно
 * проверять все доступные ему действия
 * прописать все возможные ошибки
 *
 */
 
if(0){
	echo '{"isOk":true,"command":"ping","response":{"isInstalled":-1}}';
}

ob_start();

$runningOn = 2;
switch ($runningOn){
    case 1: // Server
        define("SYSTEM_PATH", "/var/server");
        break;
    case 2: // Home
        define("SYSTEM_PATH", ".");
        break;
    case 3: // Server QA
        define("SYSTEM_PATH", "/home/QAServer");
        break;
    case 5: // Server QA
        define("SYSTEM_PATH", "/var/server");
        break;
}

require_once(SYSTEM_PATH . "/System/settings.php");
require_once(SYSTEM_PATH . "/System/function.php");

track_stats(); // Отслеживаем производительность

register_shutdown_function('shutdown');
set_error_handler("handlerError");

track_stats(); // Отслеживаем производительность

//register_tick_function("track_stats"); 
// declare(ticks = 1);

while(true){

    $return = array();
    $actionResult = NULL;

    if(!$_POST){
        $actionResult = new ErrorPoint(ErrorPoint::CODE_FORBIDDEN_REQUEST, "Не разрещенная передача данных", ErrorPoint::TYPE_USER);
        break;
    }

    $checkSum     = isset($_POST["checksum"]) ? $_POST["checksum"] : 0;
    $referrerId   = isset($_POST["referrerId"]) ? $_POST["referrerId"] : 0;
    $userId       = isset($_POST["id"]) ? $_POST["id"] : 0;
    $authKey      = isset($_POST["authKey"]) ? $_POST["authKey"] : 0;
    $command      = isset($_POST["command"]) ? $_POST["command"] : 0; 
    $groupId      = isset($_POST["groupId"]) ? $_POST["groupId"] : 0;

    if(empty($checkSum) || empty($userId) || empty($authKey) || empty($command)){
        $actionResult = new ErrorPoint(ErrorPoint::CODE_PARAMETERS, "Не полный запрос", ErrorPoint::TYPE_USER);
        break;
    }

    if(!in_array($command, $allowCommands)){
        $actionResult = new ErrorPoint(ErrorPoint::CODE_BAD_COMMAND, "Команда запрещена (command: $command)", ErrorPoint::TYPE_USER);
        break;
    }

    $serverCheckSum = md5($authKey . SECRET_KEY . $userId);

    if($serverCheckSum != $checkSum){
        $actionResult = new ErrorPoint(ErrorPoint::CODE_BAD_MD5, "WFT", ErrorPoint::TYPE_USER); 
    }

    $VKCheckSum = md5(VK_API_ID . "_" . $userId . "_" . VK_API_SECRET);
    if(!in_array(getRealIP(), $allowIPForSystemCommand) && $authKey != $VKCheckSum){
        $actionResult = new ErrorPoint(ErrorPoint::CODE_BAD_MD5, "WFT!: " . getRealIP(), ErrorPoint::TYPE_USER); 
    }
 
    if($runningOn != 2 && $_SERVER['SERVER_ADDR'] != "109.234.155.18" && isset($_SERVER['HTTP_REFERER']) && strpos ( $_SERVER['HTTP_REFERER'], "vkontakte.ru") === false){ 
        $actionResult = new ErrorPoint(ErrorPoint::CODE_BAD_MD5, "Нарушение изолированной среды", ErrorPoint::TYPE_USER);
    }
 
    $parameters = onActionParameters($_POST["params"]);
    if(isset($_POST["statistic"])){
        $statistic = onActionParameters($_POST["statistic"]);
    }

    GlobalParameters::setCommand($command);
    GlobalParameters::setGroupId($groupId);
    UserParameters::setUserId($userId);
    UserParameters::setAuthKey($authKey);

    if(SQL::getInstance()->connect_error){
        $actionResult = new ErrorPoint(ErrorPoint::CODE_SQL, "Невозможно подключиться (" . SQL::getInstance()->connect_error . ")", ErrorPoint::TYPE_SYSTEM);
        break;
    }

    track_stats(); // Отслеживаем производительность

    if(!($actionResult instanceof ErrorPoint)){

        switch ($command) {

            case COMMAND_PING:
                $controller = new PingController($parameters);
                break;

            case COMMAND_FRIEND_INFO:
                $controller = new FreeFriendsController($parameters);
                break;

            case COMMAND_FRIEND_TEAM:
                $controller = new FriendTeamsController($parameters);
                break;

            case COMMAND_WELCOME:
                $controller = new WelcomeController($parameters);
                break;

            case COMMAND_SAVE_SPONSORS:
                $controller = new SponsorsController($parameters);
                break;

            case COMMAND_CUZTOM:
                $controller = new CuztomizationController($parameters);
                break;

            case COMMAND_BUY_FOOTBALLER:
                $controller = new ShopController($parameters);
                break;

            case COMMAND_SAVE_TEAM:
                $controller = new TeamController($parameters);
                break;

            case COMMAND_GET_EMENY:
                $controller = new EnemyController($parameters);
                break;

            case COMMAND_GET_MATCH_RESULT:
                $controller = new MatchController($parameters);
                break;

            case COMMAND_GET_GROUPS:
                $controller = new GetTourController($parameters);
                break;

            case COMMAND_DROP_ITEM:
                $controller = new SellController($parameters);
                break;

            case COMMAND_BANK:
                $controller = new BankController($parameters);
                break;

            case COMMAND_FRIEND_IN_TEAM:
                $controller = new FriendInTeamController($parameters);
                break;

            case COMMAND_UPDATE_ENERGY:
                $controller = new GetEnergyController($parameters);
                break;

            case COMMAND_SEND_GIFT:
                $controller = new SendGiftController($parameters);
                break;

            case COMMAND_SET_AS_STAR:
                $controller = new SetSuperController($parameters);
                break;

            case COMMAND_BUY_STADIUM:
                $controller = new BuyStadiumController($parameters);
                break;

            case COMMAND_BUY_STUDY_POINTS:
                $controller = new BuyStudyPointsController($parameters);
                break;

            case COMMAND_FRESH_ENERGY:
                $controller = new FreshEnergyController($parameters);
                break;

            case COMMAND_GET_TEAM_INFO:
                $controller = new TeamProfileController($parameters);
                break;

            case COMMAND_SYSTEM:
                $controller = new SystemController($parameters);
                break;

            default:
                $actionResult = new ErrorPoint(ErrorPoint::CODE_SYSTEM, "Неразрешенная команда", ErrorPoint::TYPE_SYSTEM);
                break(2);
        }

        track_stats(); // Отслеживаем производительность

        if($controller->getCurrentError() instanceof ErrorPoint){
            $return["error"] = $controller->getCurrentError();
        }else{
            $actionResult = $controller->action();
        }

        if(isset($statistic)){
            $controller->accountingStatistic($statistic);
        }

    }

    break;
}

if($actionResult instanceof ErrorPoint){
    $return["error"] = $actionResult->getMessage();
}else{
    $return["isOk"] = true; 
    $return["command"] = $command;
    $return["response"] = $controller->getResult();
}

track_stats(); // Отслеживаем производительность

echo json_encode($return);

//$output = ob_get_contents();

// Utils::forDebug("countOfQuery: " . SQL::getInstance()->countOfQuery());

ob_end_flush();

/*if(1 || isset($command) && $command != COMMAND_SYSTEM && ErrorPoint::$isNeedToLog){
    logUsersActivity($output);
}*/

track_stats(); // Отслеживаем производительность
?> 