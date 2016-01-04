<?php
/**
 * User: Ivan
 * Date: 22.06.2010
 * Time: 17:34:58
 */

/*function getItemById($id) {
    static $page;
    if(!$page){
        $page = simplexml_load_file(ITEMS_XML);
    }
    $result = $page->xpath("/items/item[@id=$id]");
    $result = (array)$result[0];
    if($result["params"]){
        $result["params"] = (array)$result["params"];
    }

    if(!count($result)){
        return false;
    }

    return $result;
}*/

function shutdown() {
    $output = ob_get_contents();
    if ( preg_match('|<phpfatalerror>.*?Fatal error: (.*)</phpfatalerror>|ms', $output, $matches) ){
        ob_end_clean();
        echo '{"error":"1000#Intertnal server error"}';
    }
}

function getLog($type = NULL){
    static $fp = array(
        LOG_USER_ERROR => false,
        LOG_USER_ACTION => false,
        LOG_SYSTEM_ERROR => false,
        LOG_USER_DEBUG => false,
        LOG_PERFORMANCE => false,
    );

    if(!$fp[$type]){
        switch ($type) {
            case LOG_USER_DEBUG:
                $fp[$type] = fopen(SYSTEM_LOGS . '/' . LOG_USER_DEBUG, 'a+');
                break;
            case LOG_USER_ERROR:
                $fp[$type] = fopen(SYSTEM_LOGS . '/' . LOG_USER_ERROR, 'a+');
                break;
            case LOG_USER_ACTION:
                $fp[$type] = fopen(SYSTEM_LOGS . '/' . LOG_USER_ACTION, 'a+');
                break;
            case LOG_SYSTEM_ERROR:
                $fp[$type] = fopen(SYSTEM_LOGS . '/' . LOG_SYSTEM_ERROR, 'a+');
                break;
            case LOG_PERFORMANCE:
                $fp[$type] = fopen(SYSTEM_LOGS . '/' . LOG_PERFORMANCE, 'a+');
                break;
            default:
                return;
        }
    }
    return $fp[$type];
}

function getErrorString($errstr){
    $errstr = trim($errstr);
    $XML =
            "<log>
    <date>" . date("Y-m-d H:i:m") . "</date>
    <uid>" . @UserParameters::getUserId() . "</uid>
    <command>" . @GlobalParameters::getCommand() . "</command>
    <ip>" . getRealIP() . "</ip>
" . get_caller_method() . "
    <body><![CDATA[
" . $errstr . "
        ]]></body>
</log>" . PHP_EOL;
    return $XML;
}

function handlerError($errno, $errstr = NULL, $errfile = NULL, $errline = NULL){
    $errstr = getErrorString($errstr);
    switch ($errno) {
        case E_USER_ERROR:
        case E_USER_WARNING:
        case E_USER_NOTICE:
            fwrite(getLog(LOG_USER_ERROR), $errstr);
            break;
        default:
            fwrite(getLog(LOG_SYSTEM_ERROR), $errstr);
            break;
    }
   // exit();
}

function __autoload($className) {
    if(file_exists(SYSTEM_PATH . "/Models/" . $className . '.php')){
        require_once SYSTEM_PATH . "/Models/" . $className . '.php';
        return false;
    }elseif(file_exists(SYSTEM_PATH . "/Controllers/" . $className . '.php')){
        require_once SYSTEM_PATH . "/Controllers/" . $className . '.php';
        return false;
    }
}

function onActionParameters($postParameters){
    $parameters = urldecode($postParameters);
    if($parameters && $parameters != "{}"){
        $parameters = str_replace('\\\\\\\\', '', $parameters);
        $parameters = str_replace('\\\\', '\\', $parameters);

        if(substr($parameters, 0, 1) == '"'){
            $parameters = substr($parameters, 1, -1);
        }
        $parametersObject = json_decode($parameters);

        if(empty($parametersObject)){
            $parameters = str_replace('\"', '"', $parameters);
            $parametersObject = json_decode($parameters);
        }

    }else{
        $parametersObject = NULL;
    }
    return $parametersObject;
}

function logUserAction(){

    $sql_template = "UPDATE user_actions SET date_sing_in = NOW() WHERE vk_id = %d ; ";

    $sql = sprintf($sql_template,
        UserParameters::getUserId()
    );

    $result = SQL::getInstance()->query($sql);
    return $result;

}

function logUserEnergy(){

    $sql_template = "UPDATE user_actions SET date = NOW() WHERE vk_id = %d ; ";

/*    $sql_template =
            "INSERT INTO user_actions (
    date,
    vk_id,
    command
) VALUES (
    NOW(),
    %d,
    '%s'
) ON DUPLICATE KEY UPDATE command='%s', date = NOW();"; */

    $sql = sprintf($sql_template,
        UserParameters::getUserId(),
        SQL::getInstance()->real_escape_string(GlobalParameters::getCommand()),
        SQL::getInstance()->real_escape_string(GlobalParameters::getCommand())
    );

    $result = SQL::getInstance()->query($sql);
    return $result;

}

function logGroupSource($groupId){

    $sql_template =
            "INSERT INTO source_groups (
    vk_group_id,
    counter
) VALUES (
    %d,
    0
) ON DUPLICATE KEY UPDATE counter= counter + 1;";

    $sql = sprintf($sql_template,
        $groupId
    );

    $result = SQL::getInstance()->query($sql);
    return $result;

}


function getRealIP($fakeIp = false) {

    $ip = (!empty($_SERVER['HTTP_CLIENT_IP']))
            ? (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
                    ? $_SERVER['HTTP_CLIENT_IP']
                    : preg_replace('/(?:,.*)/', '', $_SERVER['HTTP_X_FORWARDED_FOR'])
            : @$_SERVER['REMOTE_ADDR'];

    $ip = (!$fakeIp)
            ? $ip
            : $fakeIp;

    // local check class b and c
    $patterns = array("/(192).(168).(\d+).(\d+)/i","/(10).(\d+).(\d+).(\d+)/i");
    foreach($patterns as $pattern) {
        if(preg_match($pattern,$ip)) {
            return "VPN";
        }
    }

    // local check class a
    $parts = explode(".",$ip);
    if($parts[0]==172 && ($parts[1]>15 || $parts[1]<32)) {
        return "VPN";
    }
    return trim($ip);
}

function get_caller_method(){
    $traces = debug_backtrace();

    $message = '    <backtrace>' . PHP_EOL;
    if (isset($traces[1])){
        if(isset($traces[1]["file"]) && !empty($traces[1]["file"])){
            $message .=
                    '        <step>
            <file>' . $traces[1]["file"] . '</file>
            <line>' . $traces[1]["line"] . '</line>
        </step>' . PHP_EOL;

        }
        if (isset($traces[2]) && isset($traces[2]['file'])){
            $message .=
                    '        <step>
            <file>' . $traces[2]['file'] . '</file>
            <line>' . $traces[2]['line'] . '</line>
        </step>'. PHP_EOL;
        }
        if (isset($traces[3]) && isset($traces[3]['file'])){
            $message .=
                    '        <step>
            <file>' . $traces[3]['file'] . '</file>
            <line>' . $traces[3]['line'] . '</line>
        </step>'. PHP_EOL;
        }
        if (isset($traces[4]) && isset($traces[4]['file'])){
            $message .=
                    '        <step>
            <file>' . $traces[4]['file'] . '</file>
            <line>' . $traces[4]['line'] . '</line>
        </step>'. PHP_EOL;
        }
    }
    $message .= '   </backtrace>' . PHP_EOL;
    return $message ;
}

function logUsersActivity($output){
    $logFile = SuperPath::get(UserParameters::getUserId(), LOG_COMMAND_PATH) . ".xml";
    $log =
            "<request>
    <date>" . date("Y-m-d H:i:m") . "</date>
    <uid>" . UserParameters::getUserId() . "</uid>
    <command>" . GlobalParameters::getCommand() . "</command>
    <body><![CDATA[
" . json_encode($_POST) . "
        ]]></body>
    <responce><![CDATA[
" . $output . "
        ]]></responce>
</request>" . PHP_EOL;

    fwrite(fopen($logFile, 'a+'), $log);
}

function getUsersActivity(){
    $logFile = SuperPath::get(UserParameters::getUserId(), LOG_COMMAND_PATH) . ".xml";
    if(file_exists($logFile)){
        echo file_get_contents($logFile);
    }else{
        echo '{"Log file does not exists"}';
    }
}


function read_header($ch, $string)
{
    global $location; #keep track of location/redirects
    global $cookiearr; #store cookies here

    global $cookie;

    $ch = getCurl();

    # ^overrides the function param $ch
    # this is okay because we need to
    # update the global $ch with
    # new cookies

    $length = strlen($string);
    if(!strncmp($string, "Location:", 9))
    { #keep track of last redirect
        $location = trim(substr($string, 9, -1));
    }
    if(!strncmp($string, "Set-Cookie:", 11))
    { #get the cookie
        $cookiestr = trim(substr($string, 11, -1));
        $cookie = explode(';', $cookiestr);
        $cookie = explode('=', $cookie[0]);
        $cookiename = trim(array_shift($cookie));
        $cookiearr[$cookiename] = trim(implode('=', $cookie));
    }
    $cookie = "";
    if(trim($string) == "")
    {  #execute only at end of header
        if (is_array($cookiearr)) {

            foreach ($cookiearr as $key=>$value)
            {
                $cookie .= trim($key) . "=" . trim($value) . ";";
            }
        }
        curl_setopt($ch, CURLOPT_COOKIE, $cookie) ;
    }

    return $length;
}

function getCurl(){

    static $ch;

    if(!$ch){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0); // For debuging
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, 'read_header');
        curl_setopt($ch, CURLINFO_HEADER_OUT, 0);
        curl_setopt($ch, CURLOPT_SSLVERSION, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; ru; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8");
        curl_setopt($ch, CURLOPT_REFERER, "http://www.mail.ru/");

        $headers = array(
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: ru,en-us;q=0.7,en;q=0.3',
            'Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7',
            'Keep-Alive: 115',
            'Connection: keep-alive',
            //'Host: www.marathonbet.com'
        );

        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    return $ch;
}

function getPage($url){

    $ch = getCurl();
    curl_setopt($ch, CURLOPT_URL, $url);
    return curl_exec($ch);

}

function copyPhotoFile($urlSrc, $destinationName){
    $cmd_command = 'convert ' . escapeshellarg($urlSrc) . '  -quality 75 -compress JPEG -coalesce ' .
            ' +repage ' . escapeshellarg(FILE_STORE_NEWS . $destinationName);

    pclose(popen($cmd_command, 'r'));;
}

function sortByParameters($teamA, $teamB){
    if($teamA->sumParameters > $teamB->sumParameters){
        return 1;
    }elseif($teamA->sumParameters < $teamB->sumParameters){
        return -1;
    }else{
        return 0;
    }
}

function sortByScore($teamA, $teamB){
    if($teamA->statistic['score'] < $teamB->statistic['score']){
        return 1;
    }elseif($teamA->statistic['score'] > $teamB->statistic['score']){
        return -1;
    }else{
        return 0;
    }
}

function getEnemyTeamsInGroup(  $teams, $currentTeam, & $scoreStore){
  
    $enemyTeam = array();

    foreach($teams as & $team){

        if($team->vk_id != $currentTeam->vk_id){

            if(isset($scoreStore[$team->vk_id . "_" . $currentTeam->vk_id])){
                $enemyScore = $scoreStore[$team->vk_id . "_" . $currentTeam->vk_id];
                $enemyTeam[$team->vk_id] = invertScore($enemyScore);

            }else if(isset($scoreStore[$currentTeam->vk_id . "_" . $team->vk_id])){
                $enemyScore = $scoreStore[$currentTeam->vk_id . "_" . $team->vk_id];
                $enemyTeam[$team->vk_id] = invertScore($enemyScore);

            }else{

                $result = Utils::detectClearChanceOfWin($currentTeam->sumParameters, $team->sumParameters);

                $goal = 1;
                $enemyGoal = 1;

                defineGoals($result, $goal, $enemyGoal);

                $enemyTeam[$team->vk_id] = new Score($result, $goal, $enemyGoal);
                $scoreStore[$team->vk_id . "_" . $currentTeam->vk_id] = $enemyTeam[$team->vk_id];

            }
        }
    }
    return $enemyTeam;
}

function defineGoals($result, & $goal, & $enemyGoal){

    switch ($result){
        case 1:
        case -1:
            $goal = rand(0, 5);
            $enemyGoal = rand(0, $goal);

            if($goal == $enemyGoal){
                $goal++;
            }

            if($result == -1){
                $temp = $goal;
                $goal = $enemyGoal;
                $enemyGoal = $temp;
            }

            break;

        case 0:
            $goal = $enemyGoal = rand(0, 5);
            break;
    }
}

function invertScore(Score $enemyScore){

    $result = ($enemyScore->result == 1)
            ? -1
            : (($enemyScore->result == -1) ? 1 : 0);

    $insertScore = new Score($result, $enemyScore->enemyGoals, $enemyScore->goals);
    return $insertScore;
}

function playToOff($playOffGroups, $tourStep, $tourType, $tourPlace){

    $newPlayOffGroups = array();

    foreach($playOffGroups as $groupNumber => $teams){

        if($groupNumber % 2 != 0){
            continue;
        }

        $enemyTeam   = $playOffGroups[$groupNumber];
        $currentTeam = $playOffGroups[$groupNumber + 1];

        $result = Utils::detectClearChanceOfWin($currentTeam->sumParameters, $enemyTeam->sumParameters, false);

        $goal = 1;
        $enemyGoal = 1;

        defineGoals($result, $goal, $enemyGoal);

        $currentTeam->score = new Score($result, $goal, $enemyGoal);
        $enemyTeam->score = invertScore($currentTeam->score);

        $currentTeam->vk_id_enemy = $enemyTeam->vk_id;
        $enemyTeam->vk_id_enemy = $currentTeam->vk_id;

        if($result > 0){
            $newPlayOffGroups[] = $currentTeam ;
        }else{
            $newPlayOffGroups[] = $enemyTeam  ;
        }

        logStep($currentTeam->vk_id, $currentTeam->vk_id_enemy, $currentTeam->score, $tourStep, $tourType, $tourPlace);
        logStep($enemyTeam->vk_id, $enemyTeam->vk_id_enemy, $enemyTeam->score, $tourStep, $tourType, $tourPlace);

    }

    return $newPlayOffGroups;

}


function logStep($vkId, $vkIdEnemy, Score $score, $step, $tourType, $tourPlace){

    $sql_template =
            "INSERT INTO tour_play_off (vk_id, vk_id_enemy, result, goals, goals_enemy, finished, play_off_step, tour_type, tour_placer_id)
VALUES(%d, %d, %d, %d, %d, 0, %d, %d, %d) ;";
    $sql = sprintf($sql_template,
        $vkId,
        $vkIdEnemy,
        $score->result,
        $score->goals,
        $score->enemyGoals,
        $step,
        $tourType,
        $tourPlace
    );

    SQL::getInstance()->query($sql);
    $stepId = SQL::getInstance()->getInsertedId();

    return $stepId;

}

function logPlaces($vkId, $tourType, $place){
 
    $SQlTeamField = $SQlField = NULL;

    switch ($tourType){
        case TOUR_TYPE_VK:      $SQlField = 'vk_place_';      $SQlTeamField = 'tour_place_vk';       $classTeamField = 'tourPlaceVK';           break;
        case TOUR_TYPE_COUNTRY: $SQlField = 'country_place_'; $SQlTeamField = 'tour_place_country';  $classTeamField = 'tourPlaceCountry';      break;
        case TOUR_TYPE_CITY:    $SQlField = 'city_place_';    $SQlTeamField = 'tour_place_city';     $classTeamField = 'tourPlaceCity';         break;
        case TOUR_TYPE_UNI:     $SQlField = 'uni_place_';     $SQlTeamField = 'tour_place_uni';      $classTeamField = 'tourPlaceUniversity';   break;
    }

    $SQlField .= $place;

    $sql_template =
"INSERT INTO tour_placer (
    vk_id,
    %s
) VALUES (
    %d,
    0
) ON DUPLICATE KEY UPDATE %s = %s + 1;";
    $sql = sprintf($sql_template,
        $SQlField,
        $vkId,
        $SQlField,
        $SQlField
    );
    SQL::getInstance()->query($sql); 


    $sql_template =  
"UPDATE teams SET %s = %d WHERE vk_id = %d";
    $sql = sprintf($sql_template,
        $SQlTeamField,
        $place,
        $vkId
    ); 
    SQL::getInstance()->query($sql);

    $teamId = $vkId;
    $team = RAM::getInstance()->getTeamById($teamId);
    if(!empty($team)){
        RAM::getInstance()->changeTeamField($teamId, $classTeamField, $place);
    }
}



function createTourGroupsByPlace($tourType){

    echo "createTourGroupsByPlace : " . $tourType . PHP_EOL ;
// todo
    switch($tourType){
        case TOUR_TYPE_COUNTRY:
            $sql_template = "SELECT DISTINCT(country) as place FROM teams WHERE country > 0 and tour_III > 0;";
  //        $sql_template = "SELECT DISTINCT(country) as place FROM teams WHERE vk_id = 100206819;";
            break;
        case TOUR_TYPE_CITY:
            $sql_template = "SELECT DISTINCT(city) as place FROM teams WHERE city > 0 and tour_III > 0 ;";
    //      $sql_template = "SELECT DISTINCT(city) as place FROM teams WHERE vk_id = 100206819;";
            break;
        case TOUR_TYPE_UNI:
            $sql_template = "SELECT DISTINCT(university) as place FROM teams WHERE university > 0 and tour_III > 0 ;";
   //     $sql_template = "SELECT DISTINCT(university) as place FROM teams WHERE vk_id = 100206819;";
            break;
    }

    $SQLResultTeams = SQL::getInstance()->query($sql_template);

    if($SQLResultTeams instanceof ErrorPoint){
        return $SQLResultTeams;
    }

    if($SQLResultTeams->num_rows){

        echo "Need to upgrade " . $SQLResultTeams->num_rows . " places " . PHP_EOL ;

        while ($res = $SQLResultTeams->fetch_object()){
            createTourGroups($tourType, $res->place);
        }
    }

}


function createTourGroups($tourType, $placeId){

    echo "createTourGroups : " . $tourType . " and Place : " . $placeId . PHP_EOL ;

    // Выбираем какой чемпионат мы хотим запустить

    switch($tourType){
        case TOUR_TYPE_VK:
            $sql_template = "SELECT vk_id, param_sum FROM teams WHERE vk_place > %d AND vk_place <= 32 and tour_III > 0 GROUP BY vk_id ORDER BY tour_III DESC LIMIT 32 ;";
            break;
        case TOUR_TYPE_COUNTRY:
            $sql_template = "SELECT vk_id, param_sum FROM teams WHERE country = %d and country_place > 0 AND country_place <= 32 and tour_III > 0 GROUP BY vk_id ORDER BY tour_III DESC LIMIT 32 ;";
            break;
        case TOUR_TYPE_CITY:
            $sql_template = "SELECT vk_id, param_sum FROM teams WHERE city = %d and city_place > 0 AND city_place <= 32 and tour_III > 0 GROUP BY vk_id ORDER BY tour_III DESC LIMIT 32 ;";
            break;
        case TOUR_TYPE_UNI:
            $sql_template = "SELECT vk_id, param_sum FROM teams WHERE university = %d and university_place > 0 AND university_place <= 32 and tour_III > 0 GROUP BY vk_id ORDER BY tour_III DESC LIMIT 32 ;";
            break;
    }

    $sql = sprintf($sql_template,
        $placeId
    );

    $SQLResultTeams = SQL::getInstance()->query($sql);

    if($SQLResultTeams instanceof ErrorPoint){
        return $SQLResultTeams;
    }

    $teamInGroups = array();

    echo "We got count of commands " . $SQLResultTeams->num_rows . " "  . PHP_EOL ;

    $checkId = array();
    $fakeTeamNotInID = "";

    if($SQLResultTeams->num_rows){

        $counter = 0;

        while ($team = $SQLResultTeams->fetch_object()){
            $teamInGroups[$counter] = $team;
            $teamInGroups[$counter]->sumParameters = $team->param_sum;
            $counter ++;

            if(isset($checkId[$team->vk_id])){
                echo("Нашлись одинаковые ИД: " . $sql. PHP_EOL);   
            }
            $checkId[$team->vk_id] = true;
            $fakeTeamNotInID = $team->vk_id . ", ";
        }

        // Получение фейковых команд

        if($SQLResultTeams->num_rows < 32){

            echo "Creaing fake " . ( 32 - $SQLResultTeams->num_rows ) . " "  . PHP_EOL ;

            $fakeTeamNotInID = substr($fakeTeamNotInID, 0, -2);

            $sql_template = "SELECT vk_id, param_sum FROM teams WHERE vk_id not in (%s) GROUP BY vk_id ORDER BY RAND() LIMIT 64 ;";

            $sql = sprintf($sql_template,
                (empty($fakeTeamNotInID) ? "0" : $fakeTeamNotInID),
                ( 32 - $SQLResultTeams->num_rows )
            );

            $SQLResultTeamsFake = SQL::getInstance()->query($sql);
            if($SQLResultTeamsFake instanceof ErrorPoint){
                return $SQLResultTeamsFake;
            }

            if($SQLResultTeamsFake->num_rows){

                while ($teamFake = $SQLResultTeamsFake->fetch_object()){
                    $teamInGroups[$counter] = $teamFake;
                    $teamInGroups[$counter]->sumParameters = $teamFake->param_sum;
                    $teamInGroups[$counter]->fake = true;
                    if(isset($checkId[$teamFake->vk_id])){
                        echo("Нашлись одинаковые ИД в фейках: " . $sql. " ". PHP_EOL);
                    }else{
                        $counter ++;
                        $checkId[$teamFake->vk_id] = true;
                    }
                    if($counter == 32){
                        break;
                    }
                }

            }
        }
    }else{
        // echo $sql . " "  . PHP_EOL ;
        return;
    }

/*
     if(count($teamInGroups) != 32){
        echo "sdf SFAAA" . count($teamInGroups);print_r($teamInGroups); exit();
    }
*/

    // сортирует группы по параметрам
 
    uasort($teamInGroups, "sortByParameters");

    $teamInGroupsTemp = array();

    foreach($teamInGroups as $teams){
        $teamInGroupsTemp[] = $teams; 
    }

    $teamInGroups = array();
    for($i = 0; $i < 8; $i++){
        $teamInGroups[] = $teamInGroupsTemp[$i];
        $teamInGroups[] = $teamInGroupsTemp[$i + 8];  
        $teamInGroups[] = $teamInGroupsTemp[$i + 16];
        $teamInGroups[] = $teamInGroupsTemp[$i + 24];
    }

    $groups = array(
        array(),
        array(),
        array(),
        array(),
        array(),
        array(),
        array(),
        array(),
    );

    $counter = 0;

    // Делим на группы

    foreach($teamInGroups as & $teams){
        $groupIndex = (integer)($counter / 4);
        $groups[$groupIndex][] = $teams;
        $counter ++;
    }

   ;
    $scoreStore = array();


    $checkId = array();
    // это группы
    foreach($groups as & $groupRE){


        $checkTotalScore = 0;
        $checkTotalScoreMinus = 0;

        // Это уже в группе бегаем
        foreach($groupRE as & $teamRE){

            if(isset($checkId[$teamRE->vk_id])){
                echo("Нашлись одинаковые ИД на стадии узнавания кто победит: " . $sql. PHP_EOL);   
            }
            $checkId[$teamRE->vk_id] = true;


            // Определяем кто играет с командой
            $teamRE->matches = getEnemyTeamsInGroup($groupRE, $teamRE, $scoreStore);

            // Определяем статистику команды
            $teamRE->statistic = array(
                "win"   => 0,
                "tie"   => 0,
                "lose"  => 0,
                "score" => 0,
            );

            foreach($teamRE->matches as & $match){

                switch($match->result){
                    case 0;
                        $teamRE->statistic['tie'] ++;
                        $teamRE->statistic['score'] += 1 ;
                        break;
                    case 1;
                        $teamRE->statistic['win'] ++;
                        $teamRE->statistic['score'] += 3 ;
                        $checkTotalScoreMinus ++;
                        break;
                    case -1;
                        $teamRE->statistic['lose'] ++;
                        $teamRE->statistic['score'] += 0 ;
                        $checkTotalScore ++;
                        break;
                }
            }
        }

        if($checkTotalScoreMinus != $checkTotalScore ){
            echo("Нереальное кол-во очков $checkTotalScoreMinus != $checkTotalScore" . PHP_EOL);
           // print_r($group);exit();
        }

    }

   /*$checkId = array();
    foreach($groups as $groupNumber => $grouper){
        foreach($grouper as $team){
            if(isset($checkId[$team->vk_id])){
                echo("Нашлись одинаковые ИД в специальной проверке: " . $team->vk_id. PHP_EOL);
                exit();
            }
            $checkId[$team->vk_id] = true;
        }
    }*/

    foreach($groups as $groupNumber => & $groupRT){
        uasort($groupRT, "sortByScore");

		$totalScr = 0;
		$totalWin = 0;
		$totalLose = 0;
		foreach($groupRT as & $teamRT){
			$totalScr += $teamRT->statistic['score'] ;
			$totalWin += $teamRT->statistic['win'] ;
			$totalLose += $teamRT->statistic['lose'] ;
		}

		if($totalWin != $totalLose){
/*			Utils::forDebug("Р’ РіСЂСѓРїРїРµ РєРѕСЃСЏРєРё");
			Utils::forDebug($group);
			Utils::forDebug($groups);*/
		} 
    }

 
    $checkId = array();

    foreach($groups as $groupNumber => $grouper){

        $inPlayOff = 0;

        // echo "Comands in group " . count( $grouper ) . " "  . PHP_EOL ;

        foreach($grouper as $team){

            if(isset($checkId[$team->vk_id])){
                echo("Нашлись одинаковые ИД в инсертах в проверке: " . $team->vk_id. PHP_EOL);

                print_r($groups); exit();

            }
            $checkId[$team->vk_id] = true;
            
            $sql_template =
                    "INSERT INTO tour_groups (vk_id, wins, ties, loses, score, group_number, tour_type, tour_placer_id, in_play_off, was_closed)
    VALUES(%d, %d, %d, %d, %d, %d, %d, %d, %d, 0) ;";
            $sql = sprintf($sql_template,
                $team->vk_id,
                $team->statistic['win'],
                $team->statistic['tie'],
                $team->statistic['lose'],
                $team->statistic['score'],
                $groupNumber,
                $tourType,
                $placeId,
                (($inPlayOff < 2) ? 1 : 0)
            );

            $SQLResult = SQL::getInstance()->query($sql);
            $tourGroupId = SQL::getInstance()->getInsertedId();

            $inPlayOff ++;

            if(!isset($team->fake)){

                foreach($team->matches as $vkIdEnemy => $match){

                    $sql_template =
                            "INSERT INTO tour_groups_details (tour_group_id, vk_id, vk_id_enemy, result, goals, goals_enemy, finished)
VALUES(%d, %d, %d, %d, %d, %d, 0) ;";
                    $sql = sprintf($sql_template,
                        $tourGroupId,
                        $team->vk_id,
                        $vkIdEnemy,
                        $match->result,
                        $match->goals,
                        $match->enemyGoals
                    );
                    $SQLResult = SQL::getInstance()->query($sql);

                }
            }
        }
    }
}


function createPlayOff($tourType, $tourPlace){

    $sql_template =
            "SELECT teams.vk_id, score, tour_group_id, group_number , teams.param_forward, teams.param_half, teams.param_safe
FROM tour_groups
JOIN teams ON teams.vk_id = tour_groups.vk_id
WHERE tour_type = %d and tour_placer_id = %d and was_closed = 0 AND in_play_off = 1 ORDER BY group_number, score DESC LIMIT 32;";

    $sql = sprintf($sql_template,
        $tourType,
        $tourPlace
    );
    $SQLResultTeams = SQL::getInstance()->query($sql);

    if($SQLResultTeams instanceof ErrorPoint){
        return $SQLResultTeams;
    }

    $tourStep = 4;

    $playOffGroupsPrepare = array();
    $playOffGroups8 = array();
    $playOffGroups4 = array();
    $playOffGroups2 = array();

    echo "We got count of commands " . $SQLResultTeams->num_rows . " "  . PHP_EOL ;

    if($SQLResultTeams->num_rows){

        $counter = 0;
        while ($team = $SQLResultTeams->fetch_object()){

            $team->sumParameters = $team->param_forward + $team->param_half + $team->param_safe;
            unset($team->param_forward );
            unset($team->param_half );
            unset($team->param_safe );
            unset($team->tour_group_id );
            $counter ++;

            $playOffGroupsPrepare[$team->group_number][] = $team;

        }

        if(count($playOffGroupsPrepare) != 8){

        }else{
 
            $teamCount = 0;
            foreach($playOffGroupsPrepare as $groupNumber => $team){

                if($groupNumber % 2 != 0){
                    $teamCount = 0;
                    continue;
                }
 
                $currentTeam =   $playOffGroupsPrepare[$groupNumber][ ( ($teamCount == 0) ? 1 : 0 ) ];
                $enemyTeam   =   $playOffGroupsPrepare[$groupNumber + 1][ ( ($teamCount != 0) ? 1 : 0 ) ];

                $result = Utils::detectClearChanceOfWin( $currentTeam->sumParameters, $enemyTeam->sumParameters, false);

                $goal = 1;
                $enemyGoal = 1;

                defineGoals($result, $goal, $enemyGoal);

                $currentTeam->score = new Score($result, $goal, $enemyGoal);
                $enemyTeam->score = invertScore($currentTeam->score);

                $currentTeam->vk_id_enemy = $enemyTeam->vk_id;
                $enemyTeam->vk_id_enemy = $currentTeam->vk_id;

   //             echo "groupNumber [" . $groupNumber . "] " . ( ($teamCount == 0) ? 1 : 0 ) . " - " .$currentTeam->vk_id . "\n";
  //              echo "groupNumber [" . ($groupNumber + 1) . "] " . ( ($teamCount != 0) ? 1 : 0 ) . " - " . $enemyTeam->vk_id . "\n";

                if($result > 0){
                    $playOffGroups8[] = $currentTeam ;
                }else{
                    $playOffGroups8[] = $enemyTeam;
                }

                logStep($currentTeam->vk_id, $currentTeam->vk_id_enemy, $currentTeam->score, $tourStep, $tourType, $tourPlace);
                logStep($enemyTeam->vk_id, $enemyTeam->vk_id_enemy, $enemyTeam->score, $tourStep, $tourType, $tourPlace);

                //----------------------------------------

                $currentTeam =  $playOffGroupsPrepare[$groupNumber + 1][ ( ($teamCount == 0) ? 1 : 0 ) ];
                $enemyTeam   =  $playOffGroupsPrepare[$groupNumber][ ( ($teamCount != 0) ? 1 : 0 ) ];


                $result = Utils::detectClearChanceOfWin($currentTeam->sumParameters,$enemyTeam->sumParameters, false);

                $goal = 1;
                $enemyGoal = 1;

                defineGoals($result, $goal, $enemyGoal);

                $currentTeam->score = new Score($result, $goal, $enemyGoal);
                $enemyTeam->score = invertScore($currentTeam->score);

                $currentTeam->vk_id_enemy = $enemyTeam->vk_id;
                $enemyTeam->vk_id_enemy = $currentTeam->vk_id;

      //          echo "groupNumber  [" . ($groupNumber + 1) . "] " . ( ($teamCount == 0) ? 1 : 0 ) . " - " . $currentTeam->vk_id . "\n";
     //           echo "groupNumber  [" . $groupNumber . "] " . ( ($teamCount != 0) ? 1 : 0 ) . " - " .$enemyTeam->vk_id . "\n";

                if($result > 0){
                    $playOffGroups8[] = $currentTeam ;
                }else{
                    $playOffGroups8[] = $enemyTeam ;
                }

                logStep($currentTeam->vk_id, $currentTeam->vk_id_enemy, $currentTeam->score, $tourStep, $tourType, $tourPlace);
                logStep($enemyTeam->vk_id, $enemyTeam->vk_id_enemy, $enemyTeam->score, $tourStep, $tourType, $tourPlace);

                $teamCount ++;

            }

            $tourStep--;
            $playOffGroups4 = playToOff($playOffGroups8, $tourStep, $tourType, $tourPlace);
            $tourStep--;
            $playOffGroups2 = playToOff($playOffGroups4, $tourStep, $tourType, $tourPlace);
            $tourStep--;
            playToOff($playOffGroups2, $tourStep, $tourType, $tourPlace);

            $teamCount = 0;

            $finalIDs = array();
            foreach($playOffGroups2 as $teams){
                $finalIDs[$teams->vk_id] = $teams->vk_id;
            }



            $finalThird = array();
            foreach($playOffGroups4 as $groupNumber => $teams){

                if(!array_key_exists($teams->vk_id, $finalIDs)){
                    $finalThird[] = $teams;
                }

            }
            playToOff($finalThird, $tourStep, $tourType, $tourPlace);
 
            $firthPlace = ($playOffGroups2[0]->score->result == 1) ? $playOffGroups2[0]->vk_id : $playOffGroups2[1]->vk_id;
            $secondPlace = ($playOffGroups2[0]->score->result == 1) ? $playOffGroups2[1]->vk_id : $playOffGroups2[0]->vk_id;

            $thirdPlace = ($finalThird[0]->score->result == 1) ? $finalThird[0]->vk_id : $finalThird[1]->vk_id;
 
            logPlaces($firthPlace, $tourType, 1);
            logPlaces($secondPlace, $tourType, 2);
            logPlaces($thirdPlace, $tourType, 3);
 
        }
    }else{
        echo $sql . PHP_EOL;
    }
}

$time = microtime(true);

function track_stats(){

    if(!PERFOMANCE_LOG){
        return;  
    }

    global $time;
 
    $exe_time = (microtime(true) - $time) * 1000;

    if($exe_time > MAX_TICK_TIME){

        $trace = debug_backtrace();
        if(isset($trace[0])){
            $func_args = isset($trace[0]["args"]) ? @implode(", ",$trace[0]["args"]) : '';
            $script_stats = array(
                "current_time" => microtime(true),
                "memory" => memory_get_usage(true),
                "file" => isset($trace[0]["file"]) ? $trace[0]["file"].': '.$trace[0]["line"] : "n/a",
                "function" => isset($trace[0]) ? $trace[0]["function"].'('.$func_args.')' : "n/a",
                "called_by" => isset($trace[1]) ? $trace[1]["function"].' in '.@$trace[1]["file"].': '.@$trace[1]["line"] : "n/a",
                "ns" => $exe_time
                );

            logPerformance($script_stats);  
        }
	} 
 
    $time = microtime(true);
}


function logPerformance($script_stats){ 
    $log =
"<log>
    <date>" . date("Y-m-d H:i:m") . "</date>
    <memory>" . $script_stats['memory'] . "</memory>
    <file>" . $script_stats['file'] . "</file>
    <function>" . $script_stats['function'] . "</function>
    <called_by>" . $script_stats['called_by'] . "</called_by>
    <microsecond>" . $script_stats['ns'] . "</microsecond>
</log>" . PHP_EOL; 
    fwrite(getLog(LOG_PERFORMANCE), $log);
}

?>