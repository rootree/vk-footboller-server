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

$actionResult = null;

if(SQL::getInstance()->connect_error){
    $actionResult = new ErrorPoint(ErrorPoint::CODE_SQL, "Соединение провалено (" . SQL::getInstance()->connect_error . ")", ErrorPoint::TYPE_SYSTEM);
    die("No connection");
}


/**
 * Обновляем места среди страны
 */


$sql_template = "SELECT country FROM teams where country != 0 GROUP BY country;";

$SQLresultCountry = SQL::getInstance()->query($sql_template);

if($SQLresultCountry instanceof ErrorPoint){
    return $SQLresultCountry;
}

echo "Need to upgrade " . $SQLresultCountry->num_rows . " country tours"  . PHP_EOL ;

if($SQLresultCountry->num_rows){

    while ($countryTour = $SQLresultCountry->fetch_object()){

        $sql_template = "SELECT vk_id, tour_III FROM teams WHERE country = %d ORDER BY tour_III DESC ;";
        $sql = sprintf($sql_template,
            $countryTour->country
        );
        $SQLresultSeelctTeam = SQL::getInstance()->query($sql);

        if($SQLresultSeelctTeam instanceof ErrorPoint){
            return $SQLresultSeelctTeam;
        }

        if($SQLresultSeelctTeam->num_rows){

            $usersPlace = 1; 
            $sql = NULL;
            
            while ($teamObject = $SQLresultSeelctTeam->fetch_object()){
 
                $sql_template = 'UPDATE teams SET country_place = %d WHERE vk_id = %d ;' . PHP_EOL;
                $sql = sprintf($sql_template,
                    $usersPlace,
                    $teamObject->vk_id
                );

                $SQLresultUpdateTeam = SQL::getInstance()->query($sql);
                if($SQLresultUpdateTeam instanceof ErrorPoint){
                    break;
                }

                $teamId = $teamObject->vk_id;
                $team = RAM::getInstance()->getTeamById($teamId);
                if(!empty($team)){
                    RAM::getInstance()->changeTeamField($teamId, 'placeCountry', $usersPlace);
                }

                usleep(250);
                $usersPlace ++;
            }
        }
    } 
}









/**
 * Обновляем места среди города
 */



$sql_template = "SELECT city FROM teams where city != 0 GROUP BY city;";

$SQLresultCountry = SQL::getInstance()->query($sql_template);

if($SQLresultCountry instanceof ErrorPoint){
    return $SQLresultCountry;
}

echo "Need to upgrade " . $SQLresultCountry->num_rows . " city tours"  . PHP_EOL ;

if($SQLresultCountry->num_rows){

    while ($countryTour = $SQLresultCountry->fetch_object()){

        $sql_template = "SELECT vk_id, tour_III FROM teams WHERE city = %d ORDER BY tour_III DESC  ;";
        $sql = sprintf($sql_template,
            $countryTour->city
        );
        $SQLresultSeelctTeam = SQL::getInstance()->query($sql);

        if($SQLresultSeelctTeam instanceof ErrorPoint){
            return $SQLresultSeelctTeam;
        }

      
        if($SQLresultSeelctTeam->num_rows){

            $usersPlace = 1;
            $sql = NULL;

            while ($teamObject = $SQLresultSeelctTeam->fetch_object()){

                $sql_template = 'UPDATE teams SET city_place = %d WHERE vk_id = %d ;' . PHP_EOL;
                $sql = sprintf($sql_template,
                    $usersPlace,
                    $teamObject->vk_id
                );

                $SQLresultUpdateTeam = SQL::getInstance()->query($sql);
                if($SQLresultUpdateTeam instanceof ErrorPoint){
                    break;
                }

                $teamId = $teamObject->vk_id;
                $team = RAM::getInstance()->getTeamById($teamId);
                if(!empty($team)){
                    RAM::getInstance()->changeTeamField($teamId, 'placeCity', $usersPlace); 
                }

                usleep(250);
                $usersPlace ++;
            }
        }
    }
}





/**
 * Обновляем места среди вуза
 */





$sql_template = "SELECT university FROM teams where university != 0 GROUP BY university;";

$SQLresultCountry = SQL::getInstance()->query($sql_template);

if($SQLresultCountry instanceof ErrorPoint){
    return $SQLresultCountry;
}

echo "Need to upgrade " . $SQLresultCountry->num_rows . " university tours"  . PHP_EOL ;

if($SQLresultCountry->num_rows){

    while ($countryTour = $SQLresultCountry->fetch_object()){

        $sql_template = "SELECT vk_id, tour_III FROM teams WHERE university = %d ORDER BY tour_III DESC  ;";
        $sql = sprintf($sql_template,
            $countryTour->university
        );
        $SQLresultSeelctTeam = SQL::getInstance()->query($sql);

        if($SQLresultSeelctTeam instanceof ErrorPoint){
            return $SQLresultSeelctTeam;
        }

        if($SQLresultSeelctTeam->num_rows){

            $usersPlace = 1;
            $sql = NULL;

            while ($teamObject = $SQLresultSeelctTeam->fetch_object()){

                $sql_template = 'UPDATE teams SET university_place = %d WHERE vk_id = %d ;' . PHP_EOL;
                $sql = sprintf($sql_template,
                    $usersPlace,
                    $teamObject->vk_id
                );

                $SQLresultUpdateTeam = SQL::getInstance()->query($sql);
                if($SQLresultUpdateTeam instanceof ErrorPoint){
                    break;
                }

                $teamId = $teamObject->vk_id;
                $team = RAM::getInstance()->getTeamById($teamId);
                if(!empty($team)){
                    RAM::getInstance()->changeTeamField($teamId, 'placeUniversity', $usersPlace);
                }
                
                usleep(250);
                $usersPlace ++;
            }
        }
    }
}






/**
 * Обновляем места среди вконтакта
 */

 

$sql_template = "SELECT vk_id, tour_III FROM teams ORDER BY tour_III DESC  ;";
$sql = $sql_template;

$SQLresultSeelctTeam = SQL::getInstance()->query($sql); 
if($SQLresultSeelctTeam instanceof ErrorPoint){
    return $SQLresultSeelctTeam;
}

echo "Upgrading for All users, count records: " . $SQLresultSeelctTeam->num_rows . " teams"  . PHP_EOL ;

if($SQLresultSeelctTeam->num_rows){

    $usersPlace = 1;
    $sql = NULL;

    while ($teamObject = $SQLresultSeelctTeam->fetch_object()){

        $sql_template = 'UPDATE teams SET vk_place = %d WHERE vk_id = %d ;' . PHP_EOL;
        $sql = sprintf($sql_template,
            $usersPlace,
            $teamObject->vk_id
        );

        $SQLresultUpdateTeam = SQL::getInstance()->query($sql);
        if($SQLresultUpdateTeam instanceof ErrorPoint){
            break;
        }

        $teamId = $teamObject->vk_id;
        $team = RAM::getInstance()->getTeamById($teamId);
        if(!empty($team)){
            RAM::getInstance()->changeTeamField($teamId, 'placeVK', $usersPlace);
        }

        usleep(250);
        $usersPlace ++;
    }
}



echo str_repeat(" ", 27) . date("[Y-m-d H:i:s.m]") . " Finished " . PHP_EOL;


?>