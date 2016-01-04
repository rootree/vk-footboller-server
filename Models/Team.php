<?php

/**
 * Description of Team
 *
 * @author Administrator
 */
class Team {

    public $level;

    public $experience;

    public $money;

    public $realMoney;

    public $studyPoints;

    public $studyPointsViaPrize;

    public $inGroup;

    public $parameterForward;

    public $parameterHalf;

    public $parameterSafe;

    public $currentEnergy;

    public $energyMax;

    public $trainerId;

    public $footballers;

    public $sponsors;

    public $teamName;

    public $teamLogoId;

    public $isInstalled = 0;

    public $inTeam;

    public $socialUserId;

    public $isAbleToChoose;

    public $userPhoto;

    public $userName;

    public $userYear;

    public $sponsorRate;

    public $price;

    public $coach;

    public $counterWon;

    public $counterChoose;

    public $counterLose;

    public $counterTie;

    public $countOfActiveFootballers = 0;

    public $stadiumId = 0;

    public $userCountry = 0;

    public $userCity = 0;

    public $userUniversity = 0;

    public $placeCountry = 0;

    public $placeCity = 0;

    public $placeUniversity = 0;

    public $placeVK = 0;

    public $tourIII = 0;

    public $stadiumInstance;

    public $isNeedDailyBonus = false;

    
    public $tourPlaceCountry = 0;

    public $tourPlaceCity = 0;

    public $tourPlaceUniversity = 0;

    public $tourPlaceVK = 0;

    public $tourNotify = 0;

    public $tourBonus = 1;

    public $tourBonusTime ;

    public $isPrized ;

    public $parametersSum ;

    public $footballersCount  = 0;
    
    public $footballersFriendsCount  = 0;

    public $sponsorsCount  = 0;

    public $totalPlace  = 0;


    public function __construct(){

        $this->footballersCount = 0;
        $this->footballersFriendsCount = 0; 
        $this->sponsorsCount = 0;
        $this->countOfActiveFootballers = 0;
        $this->stadiumId = 0;
        $this->userCountry = 0;
        $this->userCity = 0;
        $this->userUniversity = 0;
        $this->placeCountry = 0;
        $this->placeCity = 0;
        $this->placeUniversity = 0;
        $this->placeVK = 0;
        $this->tourIII = 0;
        $this->isNeedDailyBonus = false;
        $this->tourPlaceCountry = 0;
        $this->tourPlaceCity = 0;
        $this->tourPlaceUniversity = 0;
        $this->tourPlaceVK = 0;
        $this->tourNotify = 0;
        $this->tourBonus = 1;

    }

    public function initById($userId){

        $this->socialUserId = intval($userId);

        if(!$this->socialUserId){
            return new ErrorPoint(ErrorPoint::CODE_VK, "Не получен номер команды", ErrorPoint::TYPE_USER);
        }

        track_stats(); // Отслеживаем производительность

        $teamInstance = RAM::getInstance()->getTeamById($this->socialUserId);

        track_stats(); // Отслеживаем производительность

        if(empty($teamInstance)){//} || GlobalParameters::$IS_FAKE_ENTER || GlobalParameters::MODER_ID == $userId){

            $sql_template = "SELECT *, unix_timestamp(tour_bonus_time) as tour_bonus_time FROM teams WHERE vk_id = %s";
            $sql = sprintf($sql_template,
                $this->socialUserId
            );
            $result = SQL::getInstance()->query($sql);

            if($result instanceof ErrorPoint){
                return $result;
            }

            track_stats(); // Отслеживаем производительность

            if($result->num_rows){

                $loadedTeam = $result->fetch_object();

                $this->isInstalled = 1;
                $this->initFromDB($loadedTeam);

                track_stats(); // Отслеживаем производительность

                $this->footballers = FootballerSatellite::initForTeam($this);
                if($this->footballers instanceof ErrorPoint){
                    return $this->footballers;
                }

                track_stats(); // Отслеживаем производительность

                $this->sponsors = SponsorSatellite::initForTeam($this);
                if($this->sponsors instanceof ErrorPoint){
                    return $this->sponsors;
                }

                return true;

            }else{

                if(GlobalParameters::getCommand() != COMMAND_PING
                        && GlobalParameters::getCommand() != COMMAND_FRIEND_INFO
                        && GlobalParameters::getCommand() != COMMAND_SYSTEM
                        && GlobalParameters::getCommand() != COMMAND_WELCOME ){
                    // Utils::forDebug("Не найдена команда по ID#" . $this->socialUserId . " " . $sql);
                    return false;
                    return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Не найдена команда по ID#" . $this->socialUserId, ErrorPoint::TYPE_SYSTEM);
                }else{
                    $this->inTeam = 0;
                    return false;
                }
            }

        }else{

            $this->initFromRAM($teamInstance);
            $this->isInstalled = 1;

            track_stats(); // Отслеживаем производительность

            $this->footballers = FootballerSatellite::initForTeam($this);
            if($this->footballers instanceof ErrorPoint){
                return $this->footballers;
            }

            track_stats(); // Отслеживаем производительность

            $this->sponsors = SponsorSatellite::initForTeam($this);
            if($this->sponsors instanceof ErrorPoint){
                return $this->sponsors;
            }

            track_stats(); // Отслеживаем производительность
            
            return true; 
        }
    }

    public function initFromRAM(Team $team){

        $this->socialUserId = $team->getSocialUserId();
        $this->setLevel($team->getLevel());
        $this->setExperience($team->getExperience());
        $this->setMoney($team->getMoney());
        $this->setRealMoney($team->getRealMoney());
        $this->setStudyPoints($team->getStudyPoints());
        $this->setStudyPointsViaPrize($team->getStudyPointsViaPrize());
        $this->setInGroup($team->getInGroup());
        $this->setParameterForward($team->getParameterForward());
        $this->setParameterHalf($team->getParameterHalf());
        $this->setParameterSafe($team->getParameterSafe());
        $this->setEnergy($team->getCurrentEnergy());
        $this->setMaxEnergy($team->getEnergyMax());
        $this->trainerId = $team->getTrainerId();
        $this->teamName = trim($team->getTeamName());
        $this->teamLogoId = $team->getTeamLogoId();
        $this->inTeam = $team->getIsInTeam();
        $this->isAbleToChoose = $team->getIsAbleToChoose();
        $this->userPhoto = trim($team->getUserPhoto());
        $this->userName = trim($team->getUserName());

        $this->counterWon = $team->getCounterWon();
        $this->counterChoose = $team->getCounterChoose();
        $this->counterLose = $team->getCounterLose();
        $this->counterTie = $team->getCounterTie();
        $this->stadiumId = $team->getStadiumId();

        $this->setUserCountry($team->getUserCountry());
        $this->setUserCity($team->getUserCity());
        $this->setUserUniversity($team->getUserUniversity());

        $this->setPlaceCountry($team->getPlaceCountry());
        $this->setPlaceCity($team->getPlaceCity());
        $this->setPlaceUniversity($team->getPlaceUniversity());
        $this->setPlaceVK($team->getPlaceVK());
        $this->setTourIII($team->getTourIII());

        $this->setDailyBonus($team->isNeedDailyBonus());

        $this->setTourPlaceCountry($team->getTourPlaceCountry());
        $this->setTourPlaceCity($team->getTourPlaceCity());
        $this->setTourPlaceUniversity($team->getTourPlaceUniversity());
        $this->setTourPlaceVK($team->getTourPlaceVK());

        $this->setTourNotify($team->getTourNotify());

        $this->setTourBonus($team->getTourBonus());
        $this->setTourBonusTime($team->getTourBonusTime());
        $this->setIsPrized($team->getIsPrized()); 
        $this->setParameterSum($team->getParameterSum());
        $this->setTotalPlace($team->getTotalPlace());
        
        $this->setAllFootballersCount($team->getAllFootballersCount());
        $this->setAllFootballersFriendsCount($team->getAllFootballersFriendsCount());

        $this->setSponsorsCount($team->getSponsorsCount());

    }

    public function initFromDB($parameters, $isNeedToStoreInRam = true){

        $this->socialUserId = !isset($parameters->vk_id) ? 0 : $parameters->vk_id;
        $this->setLevel(!isset($parameters->level) ? 0 : $parameters->level);
        $this->setExperience(!isset($parameters->experience) ? 0 : $parameters->experience);
        $this->setMoney(!isset($parameters->money) ? 0 : $parameters->money);
        $this->setRealMoney(!isset($parameters->money_real) ? 0 : $parameters->money_real);
        $this->setStudyPoints(!isset($parameters->stady_point) ? 0 : $parameters->stady_point);
        $this->setStudyPointsViaPrize(!isset($parameters->prize_stady_point) ? 0 : $parameters->prize_stady_point);
        $this->setInGroup(!isset($parameters->in_group) ? 0 : $parameters->in_group);
        $this->setParameterForward(!isset($parameters->param_forward) ? 0 : $parameters->param_forward);
        $this->setParameterHalf(!isset($parameters->param_half) ? 0 : $parameters->param_half);
        $this->setParameterSafe(!isset($parameters->param_safe) ? 0 : $parameters->param_safe);
        $this->setEnergy(!isset($parameters->energy) ? 0 : $parameters->energy);
        $this->setMaxEnergy(!isset($parameters->energy_max) ? 0 : $parameters->energy_max);
        $this->trainerId = !isset($parameters->trainer_id) ? 0 : $parameters->trainer_id;
        $this->teamName = trim(!isset($parameters->team_name) ? 0 : $parameters->team_name);
        $this->teamLogoId = !isset($parameters->team_logo_id) ? 0 : $parameters->team_logo_id;
        $this->inTeam = !isset($parameters->in_team) ? 0 : $parameters->in_team;
        $this->isAbleToChoose = !isset($parameters->able_to_choose) ? 0 : $parameters->able_to_choose;
        $this->userPhoto = trim(!isset($parameters->user_photo) ? 0 : $parameters->user_photo);
        $this->userName = trim(!isset($parameters->user_name) ? 0 : $parameters->user_name);

        $this->counterWon = !isset($parameters->counter_won) ? 0 : $parameters->counter_won;
        $this->counterChoose = !isset($parameters->counter_choose) ? 0 : $parameters->counter_choose;
        $this->counterLose = !isset($parameters->counter_lose) ? 0 : $parameters->counter_lose;
        $this->counterTie = !isset($parameters->counter_tie) ? 0 : $parameters->counter_tie;
        $this->stadiumId = !isset($parameters->stadium_id) ? 0 : $parameters->stadium_id;

        $this->setUserCountry(!isset($parameters->country) ? 0 : $parameters->country);
        $this->setUserCity(!isset($parameters->city) ? 0 : $parameters->city);
        $this->setUserUniversity(!isset($parameters->university) ? 0 : $parameters->university);

        $this->setPlaceCountry(!isset($parameters->country_place) ? 0 : $parameters->country_place);
        $this->setPlaceCity(!isset($parameters->city_place) ? 0 : $parameters->city_place);
        $this->setPlaceUniversity(!isset($parameters->university_place) ? 0 : $parameters->university_place);
        $this->setPlaceVK(!isset($parameters->vk_place) ? 0 : $parameters->vk_place);
        $this->setTourIII(!isset($parameters->tour_III) ? 0 : $parameters->tour_III);

        $this->initDailyBonus(!isset($parameters->daily_bonus) ? 0 : $parameters->daily_bonus);

        $this->setTourPlaceCountry(!isset($parameters->tour_place_country) ? 0 : $parameters->tour_place_country);
        $this->setTourPlaceCity(!isset($parameters->tour_place_city) ? 0 : $parameters->tour_place_city);
        $this->setTourPlaceUniversity(!isset($parameters->tour_place_uni) ? 0 : $parameters->tour_place_uni);
        $this->setTourPlaceVK(!isset($parameters->tour_place_vk) ? 0 : $parameters->tour_place_vk);
 
        $this->setTourNotify(!isset($parameters->tour_notify) ? 0 : $parameters->tour_notify);

        $this->setTourBonus(!isset($parameters->tour_bonus) ? 0 : $parameters->tour_bonus);
        $this->setTourBonusTime(!isset($parameters->tour_bonus_time) ? 0 : $parameters->tour_bonus_time);
        $this->setIsPrized(!isset($parameters->is_prized) ? 0 : $parameters->is_prized);

        $this->setParameterSum(!isset($parameters->param_sum) ? 0 : $parameters->param_sum);
        $this->setTotalPlace(!isset($parameters->total_place) ? 0 : $parameters->total_place);

        if($isNeedToStoreInRam){
            RAM::getInstance()->setTeam($this);
        }

    }

    public function install(){

        $this->setLevel(GlobalParameters::START_LEVEL);
        $this->setExperience(0);
        $this->setMoney(GlobalParameters::START_MONEY);
        $this->setRealMoney(GlobalParameters::START_REAL_MONEY);
        $this->setEnergy(LevelsGrid::getInstance()->getBaseEnergy(GlobalParameters::START_LEVEL));
        $this->setMaxEnergy(LevelsGrid::getInstance()->getBaseEnergy(GlobalParameters::START_LEVEL));
        $this->setParameterSum($this->getParameterForward() + $this->getParameterHalf() + $this->getParameterSafe());
        
        $this->trainerId = 0;

        $this->inTeam = 0;
        $this->isInstalled = 1;

        $this->counterWon = 0;
        $this->counterChoose = 0;
        $this->counterLose = 0;
        $this->counterTie = 0;

        $this->isAbleToChoose = ($this->getActiveCount() == GlobalParameters::MAX_TEAM) ? 1 : 0;

        $sql_template =
                "INSERT INTO teams (
    date_reg,

    vk_id,
    team_name,
    team_logo_id,
    auth_key,
    energy,
    
    level,
    money,
    money_real,
    stady_point,
    in_team,
    
    able_to_choose,
    param_forward,
    param_half,
    param_safe,
    energy_max,

    user_photo,
    user_year,
    user_country,
    user_name,

    stadium_id,

    `country`,
    `city`,
    `university`,
    `param_sum`,
    `tour_notify`

) VALUES (
    NOW(),

    %d,
    '%s',
    %d,
    '%s',
    %d,

    %d,
    %d,
    %d,
    %d,
    %d,

    %d,
    " . $this->getParameterForward() . ",
    " . $this->getParameterHalf() . ",
    " . $this->getParameterSafe() . ",
    " . $this->getEnergyMax() . ",

    '" . SQL::getInstance()->real_escape_string($this->userPhoto) . "',
    %d,
    %d,
    '" . SQL::getInstance()->real_escape_string($this->userName) . "',

    " . $this->getStadiumId() . ",

    " . $this->getUserCountry() . ",
    " . $this->getUserCity() . ",
    " . $this->getUserUniversity() . ",
    " . $this->getParameterSum() . ",
    %d
     
)";


        $periodType = RAM::getInstance()->getPeriodType();

        if(empty($periodType)){
            $tourTimer = TourSatellite::getTimerDate();
            $periodType = $tourTimer->periodType;
            RAM::getInstance()->setPeriodType($periodType);

        }

        $sql = sprintf($sql_template,
            UserParameters::getUserId(),
            SQL::getInstance()->real_escape_string($this->teamName),
            $this->teamLogoId,
            SQL::getInstance()->real_escape_string(UserParameters::getAuthKey()),
            $this->getCurrentEnergy(),

            $this->getLevel(),
            $this->getMoney(),
            $this->getRealMoney(),
            $this->getStudyPoints(),
            $this->getStudyPointsViaPrize(),
            0,

            $this->isAbleToChoose,

            $this->userYear,
            $this->userCountry,
            $periodType
        );
  
        RAM::getInstance()->setTeam($this);

        $result = SQL::getInstance()->query($sql);
        if($result instanceof ErrorPoint){
            return $result;
        }

         $sql_template =
 "INSERT INTO user_actions (
    date,
    date_sing_in,
    vk_id,
    command
) VALUES (
    NOW(),
    NOW(),
    %d,
    '%s') ";
 
        $sql = sprintf($sql_template,
            UserParameters::getUserId(),
            SQL::getInstance()->real_escape_string(GlobalParameters::getCommand())
        );
        
        $result = SQL::getInstance()->query($sql);
        if($result instanceof ErrorPoint){
            return $result;
        }
        
    }

    public function getExperience(){
        return $this->experience;
    }

    public function getStudyPointsViaPrize(){
        return $this->studyPointsViaPrize;
    }

    public function getInGroup(){
        return $this->inGroup;
    }

    public function getCounterWon(){
        if($this->counterWon == 0){
            $this->counterWon = 1;
        }
        return $this->counterWon;
    }

    public function getCounterChoose(){
        return $this->counterChoose;
    }

    public function getCounterLose(){
        if($this->counterLose == 0){
            return 1;
        }
        return $this->counterLose;
    }

    public function getCounterTie(){
        return $this->counterTie;
    }

    public function getStudyPoints(){
		if($this->studyPoints < 0){
			$this->studyPoints = 0;
		}
        return $this->studyPoints;
    }

    public function getEnergyMax(){
        if($this->energyMax == 0){
            $this->setEnergyMax(floor(LevelsGrid::getInstance()->getBaseEnergy($this->getLevel()) * $this->getSponsorRage()));
        }
        return $this->energyMax;
    }

    public function getTeamName(){
        return $this->teamName;
    }

    public function getTeamLogoId(){
        return $this->teamLogoId;
    }

    public function getTrainerId(){
        return $this->trainerId;
    }


    public function getSocialUserId(){
        return $this->socialUserId;
    }

    public function getIsInstalled(){
        return $this->isInstalled;
    }

    public function getLevel(){
        return $this->level;
    }

    public function getMoney(){
        return $this->money;
    }

    public function getRealMoney(){
        return $this->realMoney;
    }


    public function getParameterForward(){
        return $this->parameterForward;
    }

    public function getParameterHalf(){
        return $this->parameterHalf;
    }

    public function getParameterSafe(){
        return $this->parameterSafe;
    }

    public function getSponsors(){
        return $this->sponsors;
    }

    public function getTitle(){
        return $this->teamName;
    }

    public function getUserPhoto(){
        return $this->userPhoto;
    }

    public function getUserName(){
        return $this->userName;
    }

    public function getIsAbleToChoose(){
        return $this->isAbleToChoose;
    }

    public function setIsAbleToChoose($set){
        $this->isAbleToChoose = $set;
    }

    public function & getFootballers(){
        return $this->footballers;
    }

    public function getFootballersFriendsCount(){
        $count = 0;
        foreach($this->footballers as  $footballer){
            if($footballer->getIsFriend()){
                $count ++;
            }
        }
        return $count;
    }

    public function getCurrentEnergy(){
        return $this->currentEnergy;
    }

    public function getIsInTeam(){
        return $this->inTeam;
    }

    public function getFanatRating(){
        $index = 1 - $this->getCounterLose() / $this->getCounterWon();
        if($index < 1){
            $index = 1;
        }else{
            if($index > 2){
                $index = 2;
            }
        }
        return $index;
    }

    public function getSponsorRage(){
        if($this->sponsorRate < 1){
            $this->setSponsorRate(1);
        }
        return $this->sponsorRate;
    }

    public function getFootballerById($id){ 
        if(isset($this->footballers[$id])){ //   воще не понимаю, почему здесь пусто
            return $this->footballers[$id];
        }else{
            return false;
        }
    }

    public function getFootballerSumLevel(){
        $levelSum = 0;
        if(is_array($this->footballers)){
            foreach($this->footballers as $footballer){
                $levelSum += $footballer->getLevel();
            }
        }
        return $levelSum;
    }

    public function setParameterForward($value){
        $this->parameterForward = ($value < 0) ? 0 : $value;
    }

    public function setParameterHalf($value){
        $this->parameterHalf = ($value < 0) ? 0 : $value;
    }

    public function setParameterSafe($value){
        $this->parameterSafe = ($value < 0) ? 0 : $value;
    }

    public function setMoney($value){
        $this->money = ($value < 0) ? 0 : $value;
    }

    public function increaseWonRating(){
        $this->counterWon ++;
    }

    public function increaseLoseRating(){
        $this->counterLose ++;
    }

    public function increaseTieRating(){
        $this->counterTie ++;
    }

    public function setRealMoney($value){
        $this->realMoney = ($value < 0) ? 0 : $value;

    }

    public function setMaxEnergy($value){
        if($value < LevelsGrid::getInstance()->getBaseEnergy($this->getLevel())){
            return $this->energyMax = floor($value);
        }else{
            return $this->energyMax = floor($value);
        }
    }

    public function setSponsorRate($value){
        if($value < 1){
            $value = 1;
        }
        return $this->sponsorRate = $value;
    }

    public function setTrainer($value){
        return $this->trainerId = $value;
    }

    public function setStudyPoints($value){
        if($value < 0){
            $value = 0;
        }
        $this->studyPoints = floor($value);
    }

    public function setStudyPointsViaPrize($value){
        $this->studyPointsViaPrize = floor($value);
    }

    public function setInGroup($value){
        $this->inGroup = floor($value);
    }

    public function setEnergy($value){
        $this->currentEnergy = intval($value);
    }

    public function setExperience($value){
        if($value < 0){
            $value = 0;
        }else{
            $this->experience = floor($value);
        }
    }

    public function setLevel($value){
        if($value < 1 || $value > GlobalParameters::LEVEL_MAX){
            $this->level = GlobalParameters::START_LEVEL;
        }else{
            $this->level = $value;
        }
    }

    public function setEnergyMax($value){
        $this->energyMax = floor($value);
    }

    public function setTeamName($value){
        $this->teamName = $value;
    }

    public function setTeamLogoId($value){
        $this->teamLogoId = $value;
    }

    public function setUserPhoto($value){
        $this->userPhoto = $value;
    }

    public function setUserName($value){
        $this->userName = $value;
    }

    public function setUserYear($value){
        $this->userYear = $value;
    }

    public function setIsInTeam($value){
        $this->inTeam = $value;
    }


    public function bindSponsors($sponsors){
        $this->sponsors = $sponsors;
    }

    public function initDailyBonus($currentDay){
        if($currentDay != date("d")){
            $this->isNeedDailyBonus = true;
        }
    }

    public function setDailyBonus($isNeedDailyBonus){
        $this->isNeedDailyBonus = $isNeedDailyBonus; 
    }

    public function setStadiumId($value){
        $this->stadiumId = $value;
    }

    public function setStadiumInstance(Stadium $stadiumInstance){
        $this->setStadiumId($stadiumInstance->getId());
        $this->stadiumInstance = $stadiumInstance;
    }

    public function getStadiumId(){
        return intval($this->stadiumId);
    }



    public function setUserCity($userCity){
        $this->userCity = $userCity;
    }

    public function getUserCity(){
        return $this->userCity;
    }

    public function setUserCountry($userCountry){
        $this->userCountry = $userCountry;
    }

    public function getUserCountry(){
        return $this->userCountry;
    }

    public function setUserUniversity($userUniversity){
        $this->userUniversity = $userUniversity;
    }

    public function getUserUniversity(){
        return $this->userUniversity;
    }



    public function setPlaceCity($placeCity){
        $this->placeCity = $placeCity;
    }

    public function getPlaceCity(){
        return $this->placeCity;
    }

    public function setPlaceCountry($placeCountry){
        $this->placeCountry = $placeCountry;
    }

    public function getPlaceCountry(){
        return $this->placeCountry;
    }

    public function setPlaceUniversity($placeUniversity){
        $this->placeUniversity = $placeUniversity;
    }

    public function setPlaceVK($placeVK){
        $this->placeVK = $placeVK;
    }

    public function getPlaceUniversity(){
        return $this->placeUniversity;
    }

    public function getPlaceVK(){
        return $this->placeVK;
    }


    public function setTourIII($tourIII){
        $this->tourIII = $tourIII;
    }

    public function getTourIII(){
        return $this->tourIII;
    }

    public function isNeedDailyBonus(){
        if($this->getStadiumId()){
            return $this->isNeedDailyBonus;
        }else{
            return $this->isNeedDailyBonus;
        }
    }

    public function getStadiumInstance(){
        if(!$this->stadiumInstance && $this->getStadiumId()){
            $this->stadiumInstance = new Stadium();
            $this->stadiumInstance->initById($this->getStadiumId());
        }
        return $this->stadiumInstance;
    }

    public function getActiveCount(){
        $countOfActiveFootballers = 0;
        if(count($this->footballers)){
            foreach($this->footballers as $footballer){
                if($footballer->getIsActive()){
                    $countOfActiveFootballers ++;
                }
            }
        }
        return $countOfActiveFootballers;
    }

    public function save(){

        if($this->getActiveCount() == GlobalParameters::MAX_TEAM){
            $this->isAbleToChoose = 1;
        }else{
            $this->isAbleToChoose = 0;
        }

        $this->setParameterSum($this->getParameterForward() + $this->getParameterHalf() + $this->getParameterSafe());
 

        $sql =
"UPDATE teams SET
  `level` = " . $this->getLevel() . ",
  `experience` = " . $this->getExperience(). ",
  `money` = " . $this->getMoney() . ",
  `money_real` = " . $this->getRealMoney() . ",
  `stady_point` = " . $this->getStudyPoints() . ",
  `energy` = " . $this->getCurrentEnergy() . ",
  `team_name` = '" . SQL::getInstance()->real_escape_string($this->getTeamName()) . "',
  `team_logo_id` = " . $this->getTeamLogoId() . ",
  `trainer_id` = " . $this->getTrainerId() . ",
  `in_team` = " . $this->getIsInTeam() . ",
  `energy_max` = " . $this->getEnergyMax() . ",
  `user_photo` = '" . SQL::getInstance()->real_escape_string($this->getUserPhoto()) . "',
  `user_name` = '" . SQL::getInstance()->real_escape_string($this->getUserName()) . "',
  `param_forward` = " . $this->getParameterForward() . ",
  `param_half` = " . $this->getParameterHalf() . ",
  `param_safe` = " . $this->getParameterSafe() . ",
  `able_to_choose` = " . $this->getIsAbleToChoose() . ",
  `in_group` = " . $this->getInGroup() . ",
  `counter_won` = " . $this->getCounterWon() . ",
  `counter_choose` = " . $this->getCounterChoose() . ",
  `counter_lose` = " . $this->getCounterLose() . ",
  `prize_stady_point` = 0,
  `stadium_id` = " . $this->getStadiumId() . ",
  `daily_bonus` = " . intval(date("d")) . ",

  `country` = " . $this->getUserCountry() . ",
  `city` = " . $this->getUserCity() . ",
  `university` = " . $this->getUserUniversity() . ",



  `tour_III` = " . $this->getTourIII() . ",
  `param_sum` = " . $this->getParameterSum() . "

WHERE
    vk_id = " . ((GlobalParameters::$IS_FAKE_ENTER == true) ? GlobalParameters::MODER_ID : UserParameters::getUserId()). " AND
    auth_key = '" . SQL::getInstance()->real_escape_string(UserParameters::getAuthKey()) . "'
";
 //  `tour_bonus` = " . $this->getTourBonus() . ",
 // `tour_bonus_time` = '" . date("Y-m-d H:i:s", ($this->getTourBonusTime() == 0 ? time() : $this->getTourBonusTime())) . "', 
        $SQLResult = SQL::getInstance()->query($sql);

        if($SQLResult instanceof ErrorPoint){
            return $SQLResult;
        }

        RAM::getInstance()->setTeam($this);

        if(GlobalParameters::$IS_FAKE_ENTER){
            RAM::getInstance()->deleteTeam(GlobalParameters::MODER_ID);
        }
    }

    public function addFootballerToStore($footballer){
        $this->footballers[] = $footballer;
    }

    public function deleteFootballerFromStore(Footballer $footballer){

        if($footballer->getIsFriend()){
            $sql_template =
                "DELETE FROM footballers_friends WHERE owner_vk_id = '%s' AND vk_id = %d";

            $sql = sprintf($sql_template,
                UserParameters::getUserId(),
                $footballer->getId()
            );

            RAM::getInstance()->deleteFootballerFriend($footballer->getId());

        }else{
            $sql_template =
                "DELETE FROM footballers WHERE owner_vk_id = '%s' AND footballer_id = %d";

            $sql = sprintf($sql_template,
                UserParameters::getUserId(),
                $footballer->getId()
            );
 
            RAM::getInstance()->deleteFootballer($footballer->getId(), UserParameters::getUserId());
        }

        $SQLresult = SQL::getInstance()->query($sql);

        if($SQLresult instanceof ErrorPoint){
            return $SQLresult;
        }

        unset($this->footballers[$footballer->getId()]);
    }

    public function updateTeamParameters(){

        $footballers = $this->getFootballers();

        $this->setParameterForward(0);
        $this->setParameterHalf(0);
        $this->setParameterSafe(0);

        if(count($footballers)){
            foreach ($footballers as $footballerInstance) {
                if($footballerInstance->getIsActive()){
                    switch($footballerInstance->getType()){
                        case TYPE_FOOTBALLER_FORWARD_CODE:
                            $this->setParameterForward($this->getParameterForward() + $footballerInstance->getLevel());
                            break;
                        case TYPE_FOOTBALLER_SAFER_CODE:
                        case TYPE_FOOTBALLER_GOALKEEPER_CODE:
                            $this->setParameterSafe($this->getParameterSafe() + $footballerInstance->getLevel());
                            break;
                        case TYPE_FOOTBALLER_HALFSAFER_CODE:
                            $this->setParameterHalf($this->getParameterHalf() + $footballerInstance->getLevel());
                            break;
                    }
                }
            }
        }
    }

    public function addExperience($xp) {

        $this->setExperience($this->getExperience() + $xp);
        $nextLevelXp = LevelsGrid::getInstance()->getNextLevelExp($this->getLevel());

        if ($this->getExperience() >= $nextLevelXp) {
            if (LevelsGrid::getInstance()->levelExist($this->getLevel() + 1)) {

                $this->setLevel($this->getLevel() + 1);
                $this->setExperience($this->getExperience() - $nextLevelXp);

                $this->setStudyPoints(
                    LevelsGrid::getInstance()->getStudyPoints($this->getLevel() - 1) + $this->getCoachMultiplay() + $this->getStudyPoints()
                );
     
                $this->setEnergyMax(floor(LevelsGrid::getInstance()->getBaseEnergy($this->getLevel()) * $this->getSponsorRage()));

                return true;
            } else {
                $this->setExperience($nextLevelXp);
            }
        }
        return false;
    }


    public function getCoach(){

        if(empty($this->coach)){
            $shopItemInDB = FootballerSatellite::getFromStoreById($this->getTrainerId(), TYPE_FOOTBALLER_TEAMLEAD_CODE);

            if($shopItemInDB instanceof ErrorPoint){
                return $shopItemInDB;
            }

            $this->coach = new TrainerPrototype($shopItemInDB);
        }
        return $this->coach;
    }

    public function getCoachMultiplay() {
        if($this->getTrainerId()){
            return $this->getCoach()->paramStudyRate;
        }else{
            return 0;
        }
    }

    public function getStudyPointCostForCount($countValue) {
        return floor($countValue * $this->getStudyPointCost() * 2.3);
    }

    public function getStudyPointRealCostForCount($countValue) {
        return floor( $this->getStudyPointCostForCount($countValue) /  GlobalParameters::REAL_VS_INGAME );
    }

    public function getStudyPointCost() {
        $baseCost = GlobalParameters::STUDY_POINT_BASE_COST;
        $studyPointCost = $baseCost + $baseCost * $this->getLevel() / 10;
        return $studyPointCost;
    }

    public function getTotalStadiumBonus() {
        if($this->getStadiumId()){
            return floor($this->getStadiumInstance()->getDailyBonus() * $this->getFanatRating() * $this->getSponsorRage());
        }else{
            return 0;
        }
    }

    static public function markTeamAsSelected($teamId, $selected = 1){

        $sql_template =
"UPDATE teams SET
  `in_team` = %d, 
  `is_prized` = 1
WHERE
    vk_id = %d
";

        $sql = sprintf($sql_template,
            $selected,
            $teamId
        );

        $SQLResult = SQL::getInstance()->query($sql);

        if($SQLResult instanceof ErrorPoint){
            return $SQLResult;
        }

        $team = RAM::getInstance()->getTeamById($teamId);
        if(!empty($team)){
            RAM::getInstance()->changeTeamField($teamId, 'inTeam', $selected);
 //           RAM::getInstance()->changeTeamField($teamId, 'isAbleToChoose', $team->getIsAbleToChoose());
            RAM::getInstance()->changeTeamField($teamId, 'isPrized', 1);
        }
    }




    public function getTourPlaceCountry(){
        return $this->tourPlaceCountry;
    }

    public function setTourPlaceCountry($tourPlaceCountry){
        $this->tourPlaceCountry = $tourPlaceCountry;
    }

    public function getTourPlaceCity(){
        return $this->tourPlaceCity;
    }

    public function setTourPlaceCity($tourPlaceCity){
        $this->tourPlaceCity = $tourPlaceCity;
    }




    public function getTourPlaceUniversity(){
        return $this->tourPlaceUniversity;
    }

    public function setTourPlaceUniversity($tourPlaceUniversity){
        $this->tourPlaceUniversity = $tourPlaceUniversity;
    }

    public function getTourPlaceVK(){
        return $this->tourPlaceVK;
    }

    public function setTourPlaceVK($tourPlaceVK){
        $this->tourPlaceVK = $tourPlaceVK;
    }



    public function getTourNotify(){
        return $this->tourNotify;
    }

    public function setTourNotify($tourNotify){
        $this->tourNotify = $tourNotify;
    }


    public function getTourBonus(){
        if($this->tourBonus > GlobalParameters::MAX_TOUR_BONUS){
            return GlobalParameters::MAX_TOUR_BONUS;
        }
        return $this->tourBonus;
    }

    public function setTourBonus($tourBonus){
        if($tourBonus > GlobalParameters::MAX_TOUR_BONUS){
            $tourBonus = GlobalParameters::MAX_TOUR_BONUS;
        }
        $this->tourBonus = $tourBonus;
    }


    public function getTourBonusTime(){
        return $this->tourBonusTime;
    }

    public function setTourBonusTime($tourBonusTime){
        $this->tourBonusTime = $tourBonusTime;
    }


    public function getIsPrized(){
        return $this->isPrized;
    }

    public function setIsPrized($isPrized){
        $this->isPrized = $isPrized;
    }


    public function getParameterSum(){
        return $this->parametersSum;
    }

    public function setParameterSum($sum){
        $this->parametersSum = $sum;
    }


    public function getSponsorsCount(){
        return $this->sponsorsCount;
    }

    public function setSponsorsCount($sum, $inRAMToo = false){
        $this->sponsorsCount = $sum;
        if($inRAMToo){
            RAM::getInstance()->changeTeamField($this->getSocialUserId(), 'sponsorsCount', $sum);  
        }
    }


    public function getAllFootballersCount(){
        return $this->footballersCount;
    }

    public function setAllFootballersCount($sum, $inRAMToo = false){
        $this->footballersCount = $sum;
        if($inRAMToo){
            RAM::getInstance()->changeTeamField($this->getSocialUserId(), 'footballersCount', $sum);  
        }
    }


    public function getTotalPlace(){
        return $this->totalPlace;
    }

    public function setTotalPlace($place){
        $this->totalPlace = $place;
    }


    public function getAllFootballersFriendsCount(){
        return $this->footballersFriendsCount;
    }

    public function setAllFootballersFriendsCount($sum, $inRAMToo = false){
        $this->footballersFriendsCount = $sum;
        if($inRAMToo){
            RAM::getInstance()->changeTeamField($this->getSocialUserId(), 'footballersFriendsCount', $sum);
        }

    }




    public function isNewTour(){
        return ($this->getTourNotify() == TOUR_NOTIFY_NEW_NOTIFIED || $this->getTourNotify() == TOUR_NOTIFY_NEW);
    }
 
}

?>
