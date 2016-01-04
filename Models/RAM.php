<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 23.06.2010
 * Time: 16:24:15
 */

class RAM extends Memcache{

    static $instance = NULL;

    var $isRAMEnabled = false;

    const RAM_TYPE_TEAM = "team";

    const RAM_TYPE_FOOTBALLER = "footballer";
    const RAM_TYPE_FOOTBALLER_FRIEND = "footballer_friend";
    const RAM_TYPE_FOOTBALLER_PROTOTYPE = "footballer_prototype";

    const RAM_TYPE_TEAM_LEAD_PROTOTYPE = "team_lead_prototype";

    const RAM_TYPE_SPONSOR = "sponsor";
    const RAM_TYPE_SPONSOR_PROTOTYPE = "sponsor_prototype";

    const RAM_TYPE_STADIUM_PROTOTYPE = "stadium_prototype";

    const RAM_TYPE_NEWS = "news";

    const RAM_TYPE_LEADERS = "liaders";

    const TOUR_START = "tour_start";
    const TOUR_FINISH = "tour_finish";
    const TOUR_PERIOD_TYPE = "PeriodType";
    
    const ENERGY_TIMER = "energy_timer";

    public function __construct() {
       $this->isRAMEnabled = false;
 //       return;
        $connectionStatus = $this->connect("localhost", 11211);
        if (!$connectionStatus) {
            $this->isRAMEnabled = false;
            return new ErrorPoint(ErrorPoint::CODE_RAM, "Соединение с Memcache сервеом провалено", ErrorPoint::TYPE_SYSTEM);;
        }
        // $this->isRAMEnabled = true;
    }

    public static function getInstance(){
        if(is_null(self::$instance)){
            self::$instance = new RAM();
        }
        return self::$instance;
    }

    function getIsServerConnected(){

        if(!$this->isRAMEnabled) return false;
        return true;
    }


//////////////////////////// Работаем с командой //////////////////////////////////////

    function getTeamById($teamId){

        if(!$this->isRAMEnabled) return null;

        $result = $this->get(RAM::RAM_TYPE_TEAM . "_" . $teamId);
        return $result;
    }

    function setTeam(Team $teamObject){

        if(!$this->isRAMEnabled) return null;


        $result = $this->set(RAM::RAM_TYPE_TEAM . "_" . $teamObject->getSocialUserId(), $teamObject);
        if($result === false){
            return new ErrorPoint(ErrorPoint::CODE_RAM, "Невозможно записать данные о команде в память", ErrorPoint::TYPE_SYSTEM);
        }
        return $result;
    }

    function deleteTeam($teamID){

        if(!$this->isRAMEnabled) return null;


         $this->delete(RAM::RAM_TYPE_TEAM . "_" . $teamID);
    }

////////////////////////////  получение лидеров //////////////////////////////////////

    function getLeaderById($entryId, $forceConnect = true){

        if(!$this->isRAMEnabled && !$forceConnect) return null;


        $link = $this->get(RAM::RAM_TYPE_LEADERS . "_" . $entryId);
        if($link === false){
            return $link;
        }
        $result = $this->get($link);
        return $result;
    }

    function setLeader(Team $object, $index, $forceConnect = true){

        if(!$this->isRAMEnabled && !$forceConnect) return null;


        $result = $this->set(RAM::RAM_TYPE_LEADERS . "_" . $index, RAM::RAM_TYPE_TEAM . "_" . $object->getSocialUserId());
        if($result === false){
            return new ErrorPoint(ErrorPoint::CODE_RAM, "Невозможно записать данные в память", ErrorPoint::TYPE_SYSTEM);
        }
        /*     $result = $this->setTeam($object);
        if($result === false){
            return new ErrorPoint(ErrorPoint::CODE_RAM, "Невозможно записать данные в память", ErrorPoint::TYPE_SYSTEM);
        }*/
        return $result;
    }



    function getLeaders($forceConnect = true){

        if(!$this->isRAMEnabled && !$forceConnect) return null;

        $leadersStore = array();
        for($i = 0; $i < 6; $i ++){
            $leader = $this->getLeaderById($i);
            if($leader === false){
                return $leadersStore;
            }
            $leadersStore[] = $leader;
        }

        return $leadersStore;
    }



////////////////////////////  получение инстанса по ИД //////////////////////////////////////


    function getFootballerById($entryId, $teamID){

        if(!$this->isRAMEnabled) return null;



        $linkName = $this->get(RAM::RAM_TYPE_FOOTBALLER . "_" . $teamID . "_" . $entryId);
        if($linkName === false){
            //new ErrorPoint(ErrorPoint::CODE_RAM, "Не найдена ссылка на футболиста №" . $entryId, ErrorPoint::TYPE_SYSTEM);
            return $linkName;
        }
		if($linkName instanceof Footballer){
			return ($linkName);
		}
        $entry = $this->get($linkName);
        return $entry;
    }

    function getFootballerFriendById($entryId){

        if(!$this->isRAMEnabled) return null;



        $linkName = $this->get(RAM::RAM_TYPE_FOOTBALLER_FRIEND . "_" . $entryId);
        if($linkName === false){
            //new ErrorPoint(ErrorPoint::CODE_RAM, "Не найдена ссылка на футболиста друга №" . $entryId, ErrorPoint::TYPE_SYSTEM);
            return $linkName;
        }
        $entry = $this->get($linkName);
        return $entry;
    }

    function getSponsorById($entryId, $teamID){

        if(!$this->isRAMEnabled) return null;



        $linkName = $this->get(RAM::RAM_TYPE_SPONSOR . "_" . $teamID  . "_" . $entryId);
        if($linkName === false){
            //new ErrorPoint(ErrorPoint::CODE_RAM, "Не найдена ссылка на спонсора №" . $entryId, ErrorPoint::TYPE_SYSTEM);
            return $linkName;
        }
        $entry = $this->get($linkName);
        return $entry;
    }





//////////////////////////// Изменение конкретного поля //////////////////////////////////////

    function changeFootballerField($footballerId, $field, $value, $inTeam){

        if(!$this->isRAMEnabled) return null;



        $footballer = $this->getFootballerById($footballerId, $inTeam);
        if(empty($footballer)){
            return false;
        }

        $footballer->$field = $value;

        return $this->setFootballer($footballer, $inTeam);

    }


    function changeFootballerFriendField($footballerId, $field, $value){

        if(!$this->isRAMEnabled) return null;



        $footballer = $this->getFootballerFriendById($footballerId);
        if(empty($footballer)){
            return false;
        }

        $footballer->$field = $value;

        return $this->setFootballerFriend($footballer);

    }

    function changeSponsorField($sponsorId, $field, $value, $inTeam){

        if(!$this->isRAMEnabled) return null;



        $sponsor = $this->getSponsorById($sponsorId, $inTeam);
        if(empty($sponsor)){
            return false;
        }
        $sponsor->$field = $value;

        return $this->setSponsor($sponsor, $inTeam);

    }

    function changeTeamField($teamId, $field, $value){

        if(!$this->isRAMEnabled) return null;



        $team = $this->getTeamById($teamId);
        if(empty($team)){
            return false;
        }

        $team->$field = $value;

        return $this->setTeam($team);

    }



//////////////////////////// Сеттеры //////////////////////////////////////


    function setFootballer(Footballer $object, $inTeam, $indexInRAM = null){

        if(!$this->isRAMEnabled) return null;



        if(is_null($indexInRAM)){
            $linkName = $this->get(RAM::RAM_TYPE_FOOTBALLER . "_" . $inTeam . "_" . $object->getId());
           // $linkName = $linkName->linkInRAM;
        }else{
            $linkName = RAM::RAM_TYPE_FOOTBALLER . "_" . $inTeam . "_" . $indexInRAM;
            $result = $this->set(RAM::RAM_TYPE_FOOTBALLER . "_" . $inTeam . "_" . $object->getId(), $linkName);
        }
 
        $result = $this->set($linkName, $object);

        if($result === false){
            return new ErrorPoint(ErrorPoint::CODE_RAM, "Невозможно записать данные футболисте в память", ErrorPoint::TYPE_SYSTEM);
        }
        return $result;
    }

    function setFootballerFriend(Footballer $object, $inTeam = null, $indexInRAM = null){

        if(!$this->isRAMEnabled) return null;



        if(is_null($inTeam) || is_null($indexInRAM)){
            $linkName = $this->get(RAM::RAM_TYPE_FOOTBALLER_FRIEND . "_" . $object->getId());
          //  $linkName = $linkName->linkInRAM;
        }else{
            $linkName = RAM::RAM_TYPE_FOOTBALLER_FRIEND . "_" . $inTeam . "_" . $indexInRAM;
            $result = $this->set(RAM::RAM_TYPE_FOOTBALLER_FRIEND . "_" . $object->getId(), $linkName);
        }

        $result = $this->set($linkName, $object);

        if($result === false){
            return new ErrorPoint(ErrorPoint::CODE_RAM, "Невозможно записать данные футболисте в память", ErrorPoint::TYPE_SYSTEM);
        }
        return $result;
    }


    function setSponsor(Sponsor $object, $inTeam, $indexInRAM = null){

        if(!$this->isRAMEnabled) return null;



        if(is_null($indexInRAM)){
            $linkName = $this->get(RAM::RAM_TYPE_SPONSOR . "_" . $inTeam . "_" . $object->getId());
        }else{
            $linkName = RAM::RAM_TYPE_SPONSOR . "_" . $inTeam . "_" . $indexInRAM;
            $result = $this->set(RAM::RAM_TYPE_SPONSOR . "_" . $inTeam . "_" . $object->getId(), $linkName);
        }

        $result = $this->set($linkName, $object);

        return $result;
    }



//////////////////////////// Удаление //////////////////////////////////////



    function deleteFootballer($entryId, $teamID){

        if(!$this->isRAMEnabled) return null;
 
        $footballer = RAM::getInstance()->getFootballerById($entryId, $teamID);

        if($footballer === false){

        }else{
            $linkName = $this->get(RAM::RAM_TYPE_FOOTBALLER . "_" . $teamID . "_" . $entryId); 
            if($linkName === false){
                return $linkName;
            }
            $this->set($linkName, null);  
        }
    }


    function deleteFootballerFriend($entryId){

        if(!$this->isRAMEnabled) return null;



        $footballer = RAM::getInstance()->getFootballerFriendById($entryId);
        if($footballer === false){

        }else{
            $linkName = $this->get(RAM::RAM_TYPE_FOOTBALLER_FRIEND . "_" . $entryId);
            if($linkName === false){
                return $linkName;
            }
            $this->set($linkName, null);
        }
    }


    function deleteSponsor($entryId, $teamID){

        if(!$this->isRAMEnabled) return null;



        $sponsor = RAM::getInstance()->getSponsorById($entryId, $teamID);
        if($sponsor === false){

        }else{
            $linkName = $this->get(RAM::RAM_TYPE_SPONSOR. "_" . $teamID . "_" . $entryId);
            if($linkName === false){
                return $linkName;
            }
            $this->set($linkName, null);
        }
    }




//////////////////////////  //////////////////////////////////


/*
    public function set($linkName, $object){

        if(!$this->isRAMEnabled) return null;


        if($linkName instanceof Footballer){
            $linkName = $linkName->linkInRAM;   
        }
        if(is_object($object)){
            $object->linkInRAM = $linkName;
        }
        
        $result = $this->replace( $linkName, $object );
        if( $result == false )
        {
            parent::set($linkName, $object);
        } 
    }*/
/*
    public function get($linkName){
        return null;
    }
*/
//////////////////////////// Вытаскивание всего сразу //////////////////////////////////////


    function getObjectsForTeam($teamId, $type){

        if(!$this->isRAMEnabled) return null;



        $objectsStore = array();
        $isExistsObjects = false;
        $count = 0;
        do{
            $linkName = $type . "_" . $teamId . "_" . $count;
            $count++;

            $isExistsObjects = $this->get($linkName);
 
            if($isExistsObjects === false){
                break;
            }

            if(is_null($isExistsObjects)){
                $isExistsObjects = true;
                continue;
            }

            $objectsStore[] = $isExistsObjects;

        }while($isExistsObjects);

        return $objectsStore;
    }


    function getMaxObjectIndexForTeam($teamId, $objectType){

        if(!$this->isRAMEnabled) return null;



        $isExistsFootballer = false;
        $count = 0;
        do{
            $linkName = $objectType . "_" . $teamId . "_" . $count;
            $isExistsFootballer = $this->get($linkName);

            if($isExistsFootballer === false){
                return $count;
            }

            $count++;
        }while($isExistsFootballer);

        return $count;
    }











//////////////////////////// прототипы //////////////////////////////////////


    function getFootballerPrototypeById($entryId){

        if(!$this->isRAMEnabled) return null;


        $result = $this->get(RAM::RAM_TYPE_FOOTBALLER_PROTOTYPE . "_" . $entryId);
        return $result;
    }

    function setFootballerPrototype($object){

        if(!$this->isRAMEnabled) return null;


        $result = $this->set(RAM::RAM_TYPE_FOOTBALLER_PROTOTYPE . "_" . $object->id, $object);
        if($result === false){
            return new ErrorPoint(ErrorPoint::CODE_RAM, "Невозможно записать данные в память", ErrorPoint::TYPE_SYSTEM);
        }
        return $result;
    }


    function getTeamLeadPrototypeById($entryId){

        if(!$this->isRAMEnabled) return null;


        $result = $this->get(RAM::RAM_TYPE_TEAM_LEAD_PROTOTYPE . "_" . $entryId);
        return $result;
    }

    function setTeamLeadPrototype($object){

        if(!$this->isRAMEnabled) return null;


        $result = $this->set(RAM::RAM_TYPE_TEAM_LEAD_PROTOTYPE . "_" . $object->id, $object);
        if($result === false){
            return new ErrorPoint(ErrorPoint::CODE_RAM, "Невозможно записать данные в память", ErrorPoint::TYPE_SYSTEM);
        }
        return $result;
    }


    function getSponsorPrototypeById($entryId){

        if(!$this->isRAMEnabled) return null;


        $result = $this->get(RAM::RAM_TYPE_SPONSOR_PROTOTYPE . "_" . $entryId);
        return $result;
    }

    function setSponsorPrototype($object){

        if(!$this->isRAMEnabled) return null;


        $result = $this->set(RAM::RAM_TYPE_SPONSOR_PROTOTYPE . "_" . $object->id, $object);
        if($result === false){
            return new ErrorPoint(ErrorPoint::CODE_RAM, "Невозможно записать данные в память", ErrorPoint::TYPE_SYSTEM);
        }
        return $result;
    }

    function getStadiumPrototypeById($entryId){

        if(!$this->isRAMEnabled) return null;


        $result = $this->get(RAM::RAM_TYPE_STADIUM_PROTOTYPE . "_" . $entryId);
        return $result;
    }

    function setStadiumPrototype($object){

        if(!$this->isRAMEnabled) return null;


        $result = $this->set(RAM::RAM_TYPE_STADIUM_PROTOTYPE . "_" . $object->id, $object);
        if($result === false){
            return new ErrorPoint(ErrorPoint::CODE_RAM, "Невозможно записать данные в память", ErrorPoint::TYPE_SYSTEM);
        }
        return $result;
    }







//////////////////////////// Новости //////////////////////////////////////



    function getNewsById($entryId, $forceConnect = true){

        if(!$this->isRAMEnabled && !$forceConnect) return null;


        $result = $this->get(RAM::RAM_TYPE_NEWS . "_" . $entryId);
        return $result;
    }

    function setNews(NewsEntry $object){

        if(!$this->isRAMEnabled) return null;


        $result = $this->set(RAM::RAM_TYPE_NEWS . "_" . $object->id, $object);
        if($result === false){
            return new ErrorPoint(ErrorPoint::CODE_RAM, "Невозможно записать данные в память", ErrorPoint::TYPE_SYSTEM);
        }
        return $result;
    }
 
    function getNews($forceConnect = true){

        if(!$this->isRAMEnabled && !$forceConnect) return null;


        $newsStore = array();
        for($i = 0; $i < 32; $i ++){
            $news = $this->getNewsById($i);
            if($news === false){
                return $newsStore;
            }
            $newsStore[] = $news;
        }
        return $newsStore;
    }



//////////////////////////// Таймеры //////////////////////////////////////



    function getTourStart($forceConnect = true){

        if(!$this->isRAMEnabled && !$forceConnect) return null;


        $result = $this->get(RAM::TOUR_START);
        return $result;
    }

    function setTourStart($time, $forceConnect = true){

        if(!$this->isRAMEnabled && !$forceConnect) return null;


        $result = $this->set(RAM::TOUR_START, $time);
        if($result === false){
            return new ErrorPoint(ErrorPoint::CODE_RAM, "Невозможно записать данные в память", ErrorPoint::TYPE_SYSTEM);
        }
        return $result;
    }

    function getTourFinish($forceConnect = true){

        if(!$this->isRAMEnabled && !$forceConnect) return null;

        $result = $this->get(RAM::TOUR_FINISH);
        return $result;
    }

    function setTourFinish($time, $forceConnect = true){

        if(!$this->isRAMEnabled && !$forceConnect) return null;


        $result = $this->set(RAM::TOUR_FINISH, $time);
        if($result === false){
            return new ErrorPoint(ErrorPoint::CODE_RAM, "Невозможно записать данные в память", ErrorPoint::TYPE_SYSTEM);
        }
        return $result;
    }


    function getPeriodType($forceConnect = true){

        if(!$this->isRAMEnabled && !$forceConnect) return null;


        $result = $this->get(RAM::TOUR_PERIOD_TYPE);
        return $result;
    }

    function setPeriodType($type, $forceConnect = true){

        if(!$this->isRAMEnabled && !$forceConnect) return null;


        $result = $this->set(RAM::TOUR_PERIOD_TYPE, $type);
        if($result === false){
            return new ErrorPoint(ErrorPoint::CODE_RAM, "Невозможно записать данные в память", ErrorPoint::TYPE_SYSTEM);
        }
        return $result;
    }

    function getEnergyLastUpdate($forceConnect = true){

        if(!$this->isRAMEnabled && !$forceConnect) return null;


        $result = $this->get(RAM::ENERGY_TIMER);
        return $result;
    }

    function setEnergyLastUpdate($time, $forceConnect = true){

        if(!$this->isRAMEnabled && !$forceConnect) return null;

        
        $result = $this->set(RAM::ENERGY_TIMER, $time);
        if($result === false){
            return new ErrorPoint(ErrorPoint::CODE_RAM, "Невозможно записать данные в память", ErrorPoint::TYPE_SYSTEM);
        }
        return $result;
    }
 
}

?>