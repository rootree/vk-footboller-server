<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 23.06.2010
 * Time: 17:40:58
 */

class FootballerSatellite {

    public static $tactic = array(
        array(TYPE_FOOTBALLER_GOALKEEPER_CODE=>1,TYPE_FOOTBALLER_FORWARD_CODE=>4,TYPE_FOOTBALLER_SAFER_CODE=>4,TYPE_FOOTBALLER_HALFSAFER_CODE=>2),
        array(TYPE_FOOTBALLER_GOALKEEPER_CODE=>1,TYPE_FOOTBALLER_FORWARD_CODE=>5,TYPE_FOOTBALLER_SAFER_CODE=>3,TYPE_FOOTBALLER_HALFSAFER_CODE=>2),
        array(TYPE_FOOTBALLER_GOALKEEPER_CODE=>1,TYPE_FOOTBALLER_FORWARD_CODE=>3,TYPE_FOOTBALLER_SAFER_CODE=>4,TYPE_FOOTBALLER_HALFSAFER_CODE=>3)
    );

    public static function getFromStoreById($id, $line){

        if($line == TYPE_FOOTBALLER_TEAMLEAD_CODE){
            $object = RAM::getInstance()->getTeamLeadPrototypeById($id);
        }else{
            $object = RAM::getInstance()->getFootballerPrototypeById($id);
        }

        if(!empty($object)){
            return $object;
        }

        if($line == TYPE_FOOTBALLER_TEAMLEAD_CODE){
            $sql_template = "SELECT * FROM item_teamleads WHERE id = %d";
        }else{
            $sql_template = "SELECT * FROM item_footballers WHERE id = %d";
        }

        $sql = sprintf($sql_template,
            intval($id)
        );

        $SQLresult = SQL::getInstance()->query($sql);

        if($SQLresult instanceof ErrorPoint){
            return $SQLresult;
        }

        if($SQLresult->num_rows == 0){
            return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Информация о данном обьекте не найдена (#SQL:" . $sql. ")", ErrorPoint::TYPE_SYSTEM);
        }

        $object = $SQLresult->fetch_object();

        if($line == TYPE_FOOTBALLER_TEAMLEAD_CODE){  
            RAM::getInstance()->setTeamLeadPrototype($object);
        }else{
            RAM::getInstance()->setFootballerPrototype($object);
        }

        return $object;
    }
 
    public static function initForTeam(Team & $team, $isGetOnlyActive = false){

        $teamId = $team->getSocialUserId();

        $store = array();

        track_stats(); // Отслеживаем производительность

        $footballersStore = RAM::getInstance()->getObjectsForTeam($teamId, RAM::RAM_TYPE_FOOTBALLER);

        track_stats(); // Отслеживаем производительность

        $activeFootballer = 0;
 //     Utils::forDebug(count($footballersStore) . " == " . $team->getAllFootballersCount());
        if(count($footballersStore) != $team->getAllFootballersCount() ||
                $team->getAllFootballersCount() == 0){//} || GlobalParameters::$IS_FAKE_ENTER || GlobalParameters::MODER_ID == $teamId){

            $sql_template =
"SELECT
    footballers.level,
    footballers.footballer_id,
    footballers.is_active,
    footballers.super,
    footballers.health_down,
    item_footballers.line as type FROM footballers
LEFT JOIN  item_footballers ON item_footballers.id = footballers.footballer_id
WHERE footballers.owner_vk_id = %d " . ($isGetOnlyActive ? " AND footballers.is_active = 1 " : "") . " ORDER BY item_footballers.line DESC ";

            $sql_template =
"SELECT
    footballers.level,
    footballers.footballer_id,
    footballers.is_active,
    footballers.super,
    footballers.health_down,
    item_footballers.line AS typer FROM footballers, item_footballers
WHERE footballers.owner_vk_id =  %d " . ($isGetOnlyActive ? " AND footballers.is_active = 1 " : "") . " AND item_footballers.id = footballers.footballer_id ORDER BY item_footballers.line ASC";

            $sql = sprintf($sql_template,
                intval($teamId)
            );
            $SQLresult = SQL::getInstance()->query($sql);

            track_stats(); // Отслеживаем производительность

            if($SQLresult instanceof ErrorPoint){
                return $SQLresult;
            }
  
            track_stats(); // Отслеживаем производительность

            $team->setAllFootballersCount($SQLresult->num_rows, true);
            
            $counter = 0;
            $footballersStore = array();

            while ($footballer = $SQLresult->fetch_object()){
                $footballerInstance = new Footballer();
                $footballerInstance->initFromParameters($footballer, false, $teamId, $sql);
                $footballersStore[$footballerInstance->getId()] = $footballerInstance;
                if($footballerInstance->getIsActive() == 1){
                    $activeFootballer ++;
                }

                //RAM::getInstance()->setFootballer($footballerInstance, $teamId, $counter);
                $counter ++;
            }
            
            track_stats(); // Отслеживаем производительность
        }else{
 
            track_stats(); // Отслеживаем производительность
            $footballersStore = array();

            if($isGetOnlyActive){
                
                $footballersStoreTeam = $footballersStore;

                foreach ($footballersStoreTeam as $footballerInstance){
                    if($footballerInstance->getIsActive() == 1){
                        $footballersStore[$footballerInstance->getId()] = $footballerInstance;
                    }
                    $footballerInstance->checkHealth(); 
                }
            }else{

                $footballersStoreTeam = $footballersStore;

                foreach ($footballersStoreTeam as $footballerInstance){
                    if($footballerInstance instanceof Footballer){ 
                        if($footballerInstance instanceof Footballer && $footballerInstance->getIsActive() == 1){
                            $activeFootballer ++;
                        }
                        $footballersStore[$footballerInstance->getId()] = $footballerInstance;
                        $footballerInstance->checkHealth();
                    }
                }
            }

            track_stats(); // Отслеживаем производительность

        }

        track_stats(); // Отслеживаем производительность

        $footballersFriendStore = RAM::getInstance()->getObjectsForTeam($teamId, RAM::RAM_TYPE_FOOTBALLER_FRIEND);

        track_stats(); // Отслеживаем производительность

        if(count($footballersFriendStore) != $team->getAllFootballersFriendsCount() || $team->getAllFootballersFriendsCount() == 0){

            $sql_template =
"SELECT footballers_friends.*,
    teams.user_year,
    teams.user_country,
    teams.user_name,
    teams.user_photo,
    teams.in_team,
    owner_team.team_name
FROM footballers_friends
JOIN teams ON footballers_friends.vk_id = teams.vk_id
LEFT JOIN teams AS owner_team ON owner_team.vk_id = footballers_friends.owner_vk_id
WHERE owner_vk_id = '%s' " . ($isGetOnlyActive ? " AND is_active = 1 " : "") . " ORDER BY footballers_friends.type DESC";

            $sql_template =
"SELECT
    footballers_friends.vk_id,
    footballers_friends.level,
    footballers_friends.type as typer,
    footballers_friends.is_active,
    footballers_friends.super,
    footballers_friends.owner_vk_id,
    footballers_friends.health_down,
    teams.user_year,
    teams.user_country,
    teams.user_name,
    teams.user_photo,
    teams.in_team
FROM footballers_friends
JOIN teams ON footballers_friends.vk_id = teams.vk_id
WHERE owner_vk_id = %d " . ($isGetOnlyActive ? " AND is_active = 1 " : "") . "  ORDER BY footballers_friends.type DESC"; // ORDER BY footballers_friends.type DESC";

            $sql = sprintf($sql_template,
                intval($teamId)
            );
            $SQLresult = SQL::getInstance()->query($sql);

            if($SQLresult instanceof ErrorPoint){
                return $SQLresult;
            }

            $team->setAllFootballersFriendsCount($SQLresult->num_rows, true);

            track_stats(); // Отслеживаем производительность

            $footballersFriendStore = array();
            
            $counter = 0;
            while ($footballer = $SQLresult->fetch_object()){
                $footballerInstance = new Footballer();
                $footballerInstance->initFromParameters($footballer, true, $teamId, $sql);
                $footballersFriendStore[$footballerInstance->getId()] = $footballerInstance;
                if($footballerInstance->getIsActive() == 1){
                    $activeFootballer ++;
                }

                //RAM::getInstance()->setFootballerFriend($footballerInstance, $teamId, $counter);
                $counter ++;
            }

            track_stats(); // Отслеживаем производительность

        }else{

            track_stats(); // Отслеживаем производительность

            if($isGetOnlyActive){
                $footballersStoreTeam = $footballersFriendStore;
                $footballersFriendStore = array();
                foreach ($footballersStoreTeam as $footballerInstance){
                    if($footballerInstance->getIsActive() == 1){
                        $footballersFriendStore[] = $footballerInstance;
                    }
                    $footballerInstance->checkHealth();
                }
            }else{
                foreach ($footballersFriendStore as $footballerInstance){
                    if($footballerInstance->getIsActive() == 1){
                        $activeFootballer ++;
                    }
                    $footballerInstance->checkHealth();
                }
            }

            track_stats(); // Отслеживаем производительность
            
        }

        $store = array_merge ( $footballersStore , $footballersFriendStore );
        $returnStore = array();
        foreach ($store as $footballerInstance){
            if($footballerInstance instanceof Footballer)
            $returnStore[$footballerInstance->getId()] = $footballerInstance;
        }

        track_stats(); // Отслеживаем производительность

        if($activeFootballer > GlobalParameters::MAX_TEAM){ 
            $actionResult = FootballerSatellite::bagoFixActiveFootballers($team, $returnStore);
            if($actionResult instanceof ErrorPoint){
                return $actionResult;
            }
            $returnStore = FootballerSatellite::initForTeam($team);

            //            return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Количество активных игроков превышет допустимого значения. Разрешено ".
            //                   GlobalParameters::MAX_TEAM . " а у игрока " . $activeFootballer, ErrorPoint::TYPE_SYSTEM);
        }

        track_stats(); // Отслеживаем производительность

        return $returnStore;
    }

    public static function bagoFixActiveFootballers($team, $store){

        $teamId = $team->getSocialUserId();

        $sql_template = "UPDATE footballers_friends SET is_active = 0 WHERE owner_vk_id = %d ";
        $sql = sprintf($sql_template,
            intval($teamId)
        );
        $SQLresult = SQL::getInstance()->query($sql);
        if($SQLresult instanceof ErrorPoint){
            return $SQLresult;
        }

        $sql_template = "UPDATE footballers SET is_active = 0 WHERE owner_vk_id = %d ";
        $sql = sprintf($sql_template,
            intval($teamId)
        );
        $SQLresult = SQL::getInstance()->query($sql);
        if($SQLresult instanceof ErrorPoint){
            return $SQLresult;
        }
// Utils::forDebug($store);

        if(RAM::getInstance()->getIsServerConnected()){

            foreach($store as $footballerInstance){

                if($footballerInstance->getIsActive() == 1 && $footballerInstance->getIsFriend() == 0){
                    RAM::getInstance()->delete(RAM::RAM_TYPE_FOOTBALLER . "_" . $teamId . "_" . $footballerInstance->getId());
                    RAM::getInstance()->delete($footballerInstance->linkInRAM);
                }

                if($footballerInstance->getIsActive() == 1 && $footballerInstance->getIsFriend() == 1){
                    RAM::getInstance()->delete(RAM::RAM_TYPE_FOOTBALLER_FRIEND . "_" . $footballerInstance->getId());
                    RAM::getInstance()->delete($footballerInstance->linkInRAM);
                }
            }
        }
        
        return ;
    }

    public static function getRandomPrototypes($randomLimit){

        $sql_template = "SELECT * FROM item_footballers WHERE required_level = 1 ORDER BY RAND() ";  
        $sql = $sql_template;
        $SQLresult = SQL::getInstance()->query($sql);

        if($SQLresult instanceof ErrorPoint){
            return $SQLresult;
        }

        $footballersStore = array();

        $selectedTactic = FootballerSatellite::$tactic[rand(0, count(FootballerSatellite::$tactic) - 1)];
        $fCount = 0;

        if($SQLresult->num_rows){
            while ($footballer = $SQLresult->fetch_object()){
                if($selectedTactic[$footballer->line] > 0){
                    self::addFootballer($footballer, $footballersStore);
                    RAM::getInstance()->setFootballerPrototype($footballer);
                    $selectedTactic[$footballer->line] --;
                    $fCount++;
                    $randomLimit -- ;
                    if($fCount == GlobalParameters::MAX_TEAM || $randomLimit == 0){
                        break;
                    }
                }
            }
        }
        return $footballersStore;
    }

    private static function addFootballer($footballer, & $footballersStore){
        $footballerPrototype = new FootballerPrototype();
        $footballerPrototype->init($footballer, null);
        $footballersStore[] = $footballerPrototype;
    }

    public static function getFootballerOwner($footballerId){
        
        $sql_template = "SELECT owner_vk_id FROM footballers_friends WHERE vk_id = %d";
        $sql = sprintf($sql_template,
            $footballerId
        );
        $SQLresult = SQL::getInstance()->query($sql);

        if($SQLresult instanceof ErrorPoint){
            return $SQLresult;
        }

        if($SQLresult->num_rows){

            $footballers = $SQLresult->fetch_object();
            return $footballers->owner_vk_id;
        }else{
            return false;
        }
    }

    public static function detectPrice(Footballer $footballerInstance, $studyPointCost){
        return $footballerInstance->getLevel() * $studyPointCost;
    }

}
