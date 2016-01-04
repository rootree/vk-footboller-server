<?php

/**
 * Description of Footballer
 *
 * @author Administrator
 */
class Footballer {

    public $footballerName;

    public $level = 1;

    public $type;

    public $id;

    public $isFriend;

    public $isActive;
    
    public $super;

    public $photoForFriend;

    public $year;

    public $country;

    public $team_name;

    public $healthDown = 0;
    
    public $teamId = 0;
    public $SQL = 0;

    public function getYear(){
        return $this->year;
    }

    public function getCountry(){
        return $this->country;
    }
 
    public function initFromParameters($footballerParameters, $isFriend = false, $teamId = 0, $SQL = 0) {
        $this->teamId = $teamId;
        $this->SQL = $SQL;
        if($isFriend){
            $this->footballerName = isset($footballerParameters->user_name) ? $footballerParameters->user_name : "";
            $this->level = $footballerParameters->level;
            $this->type = (isset($footballerParameters->typer) ? $footballerParameters->typer : $footballerParameters->type);
            $this->id = $footballerParameters->vk_id;
            $this->isFriend = true;
            $this->isActive = $footballerParameters->is_active;
            $this->super = $footballerParameters->super;
            $this->photoForFriend = isset($footballerParameters->user_photo) ? $footballerParameters->user_photo : "";
            $this->year = isset($footballerParameters->user_year) ? $footballerParameters->user_year : "";
            $this->country = isset($footballerParameters->user_country) ? $footballerParameters->user_country : "";
            $this->team_name = (isset($footballerParameters->team_name)) ? $footballerParameters->team_name : '';
            $this->healthDown = (isset($footballerParameters->health_down)) ? $footballerParameters->health_down : 0;
        }else{
            $this->footballerName = NULL;
            $this->level = $footballerParameters->level;
            $this->type = $footballerParameters->typer;
            $this->id = $footballerParameters->footballer_id;
            $this->isFriend = false;
            $this->isActive = $footballerParameters->is_active;
            $this->super = $footballerParameters->super;
            $this->photoForFriend = NULL;
            $this->year = NULL;
            $this->country = NULL;
            $this->healthDown = $footballerParameters->health_down ;
        }

        if($teamId == UserParameters::getUserId()){
            $this->checkHealth();
        }

    }

    public function checkHealth(){
        if($this->healthDown != 0 && $this->healthDown != date("d")){
            $this->setHealthDown(0);
            $this->update();
     /*       if($this->isFriend){
                 RAM::getInstance()->changeFootballerFriendField($this->id, "healthDown", 0, $this->team_name);
            }else{
                 RAM::getInstance()->changeFootballerField($this->id, "healthDown", 0, UserParameters::getUserId());
            } */
        }
    }
      
    public function getFootballerName(){
        return $this->footballerName;
    }

    public function getLevel(){
        return $this->level;
    }

    public function getType(){
        return $this->type;
    }

    public function getIsSuper(){
        return $this->super;
    }

    public function getHealthDown(){
        return $this->healthDown;
    }

    public function setHealthDown($day){
        $this->healthDown = $day;
    }

    public function getTypeString(){
        return $this->type;
    }

    public function getId(){
        return $this->id;
    }

    public function getIsFriend(){
        return $this->isFriend;
    }

    public function getTeamName(){
        return $this->team_name;
    }

    public function getIsActive(){
        return $this->isActive;
    }

    public function getPhotoForFriend(){
        return $this->photoForFriend;
    }

    public function setLevel($value){
        $this->level = $value;
    }

    public function setType($value){
        $this->type = $value;
    }

    public function setActive($value){
        $this->isActive = $value;
    }

    public function setAsSuper($value){
        $this->super = intval($value);
    }

    public function add(FootballerPrototype $footballerStructure, $activeCount){

        $this->level = $footballerStructure->getParamLevel();
        $this->type = $footballerStructure->getLine();
        $this->id = $footballerStructure->getId();
        $this->isActive = ($activeCount < GlobalParameters::MAX_TEAM)? 1 : 0; 
        $this->isFriend = false;
        $this->super = false;

        //$currentIndex = RAM::getInstance()->getMaxObjectIndexForTeam(UserParameters::getUserId(), RAM::RAM_TYPE_FOOTBALLER);;
        //RAM::getInstance()->setFootballer($this, UserParameters::getUserId(), $currentIndex);
 
        return $this->save();

    }

    public function addFriend($footballerId, $footballerParameters, $activeCount){

        $this->level = 0;
        $this->type = 0;
        $this->id = $footballerId;
        $this->isFriend = true;
        $this->super = false;
        $this->isActive = ($activeCount < GlobalParameters::MAX_TEAM)? 1 : 0;

        $result = $this->saveFriend();

        $footballers = null;//;RAM::getInstance()->getFootballerFriendById($footballerId);
        if(empty($footballers)){
            $this->loadFromDB($footballerId); 
        }else{
            RAM::getInstance()->setFootballerFriend($this);  
        }
 
        return $result;

    }

    public function saveFriend(){

        $sql_template =
                "INSERT INTO footballers_friends (
    `vk_id`,
    `type`,
    `level`,
    `is_active`, 
    `owner_vk_id`
) VALUES (
    %d,
    %d,
    %d,
    %d, 
    %d
)";

        $sql = sprintf($sql_template,
            $this->id,
            $this->type,
            $this->level,
            $this->isActive,
            UserParameters::getUserId()
        );

        return SQL::getInstance()->query($sql);
    }

    public function save(){

        $sql_template =
            "INSERT INTO footballers (
    `footballer_id`,
    `owner_vk_id`,
    `level`,
    `is_active`
) VALUES (
    %d,
    %d,
    %d, 
    %d
)";

        $sql = sprintf($sql_template,
            $this->id,
            UserParameters::getUserId(),
            $this->level,
            $this->isActive 
        );

        return SQL::getInstance()->query($sql);
    }

    public function update(){

        if($this->teamId != UserParameters::getUserId()){
            return new ErrorPoint(ErrorPoint::CODE_SECURITY, "Техническая ошибка" , ErrorPoint::TYPE_SYSTEM);;
        }

        if($this->getIsFriend()){

            $sql_template =
                    "UPDATE footballers_friends SET
level = %d,
is_active = %d,
type = %d,
super = %d,
health_down = %d,
logger = '%s'
WHERE
owner_vk_id = %d AND
vk_id = %d";
            $sql = sprintf($sql_template,
                $this->getLevel(),
                $this->getIsActive(),
                $this->getType(),
                $this->getIsSuper(),
                $this->getHealthDown(),
                get_caller_method() . "\n\n" . json_encode(JSONPrepare::footballer($this)) . $this->teamId . "|" . UserParameters::getUserId(). $this->SQL,
                UserParameters::getUserId(),
                $this->getId()
            );

/*            $footballerInRAM = RAM::getInstance()->getFootballerFriendById($this->getId());
            if($footballerInRAM === false){
                $currentIndex = RAM::getInstance()->getMaxObjectIndexForTeam(UserParameters::getUserId(), RAM::RAM_TYPE_FOOTBALLER_FRIEND);
                RAM::getInstance()->setFootballerFriend($this, UserParameters::getUserId(), $currentIndex); 
            }else{
                RAM::getInstance()->setFootballerFriend($this);
            }*/
 
        }else{

            $sql_template =
                    "UPDATE footballers SET
level = %d,
is_active = %d,
super = %d,
health_down = %d,
logger = '%s'
WHERE
owner_vk_id = %d AND
footballer_id = %d";
            $sql = sprintf($sql_template,
                $this->getLevel(),
                $this->getIsActive(),
                $this->getIsSuper(),
                $this->getHealthDown(),
                get_caller_method() . "\n\n" . json_encode(JSONPrepare::footballer($this)) . $this->teamId . "|" . UserParameters::getUserId(). $this->SQL,
                UserParameters::getUserId(),
                $this->getId()
            );

/*            $footballerInRAM = RAM::getInstance()->getFootballerById($this->getId(), UserParameters::getUserId());
    
            if($footballerInRAM === false){ 
                $currentIndex = RAM::getInstance()->getMaxObjectIndexForTeam(UserParameters::getUserId(), RAM::RAM_TYPE_FOOTBALLER);;
                RAM::getInstance()->setFootballer($this, UserParameters::getUserId(), $currentIndex);
            }else{
                RAM::getInstance()->setFootballer($this, UserParameters::getUserId());
            }*/
            
        }
 
        $result = SQL::getInstance()->query($sql);

        if($result instanceof ErrorPoint){
            return $result;
        }
    }


    private function loadFromDB($friendId){

        $sql_template =
"SELECT footballers_friends.*,
    teams.user_year,
    teams.user_country,
    teams.user_name,
    teams.user_photo,
    teams.in_team
FROM footballers_friends
JOIN teams ON footballers_friends.vk_id = teams.vk_id
WHERE footballers_friends.vk_id = '%s'";

        $sql = sprintf($sql_template,
            intval($friendId)
        );
        $SQLresult = SQL::getInstance()->query($sql);

        if($SQLresult instanceof ErrorPoint){
            return $SQLresult;
        }

        $footballer = $SQLresult->fetch_object();
        $this->initFromParameters($footballer, true, $friendId);

/*        $footballerInRAM = RAM::getInstance()->getFootballerFriendById($friendId);
        if($footballerInRAM === false){
            $currentIndex = RAM::getInstance()->getMaxObjectIndexForTeam(UserParameters::getUserId(), RAM::RAM_TYPE_FOOTBALLER_FRIEND);
            RAM::getInstance()->setFootballerFriend($this, UserParameters::getUserId(), $currentIndex);
        }else{
            RAM::getInstance()->setFootballerFriend($this);
        }*/



    }

}
?>
