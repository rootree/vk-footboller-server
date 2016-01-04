<?php
// +----------------------------------------------------------------------+
// | IsMyFamily.name - History of your family                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2009 - 2010                                            |
// +----------------------------------------------------------------------+
// | Authors: Ivan Chura <ivan.chura@gmail.com>                           |
// +----------------------------------------------------------------------+

/**
 * @version 1.0
 * @author Ivan Chura <ivan.chura@gmail.com>  
 */
class WelcomeController extends Controller implements IController {

    public function getResult(){
        $this->result["teamProfiler"] = JSONPrepare::team($this->teamProfile); 
        return $this->result;
    }

    public function action(){

        if(!isset($this->parameters->teamInfo)){
            Utils::forDebug($this->parameters, true);
        }

        return $actionResult = $this->install(isset($this->parameters->teamInfo) ? $this->parameters->teamInfo : NULL);
    }

    private function install($teamParam){

        $team = json_decode($teamParam);

        /*if(empty($team)){
            return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Информация о команде получена неполностью, повторите попытку", ErrorPoint::TYPE_USER, $teamParam);  
        }*/

        track_stats(); // Отслеживаем производительность

        $actionResult = $this->teamProfile->initById(UserParameters::getUserId());
        if($actionResult instanceof ErrorPoint){ 
            return $actionResult;
        }
        
        if($actionResult === true){
            return; 
        }

        SQL::getInstance()->autocommit(false);
 
        $addOnStudyPoints = 0;


        $this->teamProfile->setTeamName(isset($team->teamName) ? $team->teamName : "Футболлер");
        $this->teamProfile->setTeamLogoId(isset($team->teamLogoId) ? $team->teamLogoId : "42326");

        $this->teamProfile->setUserPhoto($this->parameters->userPhoto);
        $this->teamProfile->setUserName($this->parameters->userName);
        $this->teamProfile->setUserYear(isset($this->parameters->userYear) ? intval($this->parameters->userYear) : 0);
        $this->teamProfile->setUserCountry(isset($this->parameters->userCountry) ? intval($this->parameters->userCountry) : 0);
        $this->teamProfile->setParameterForward(0);
        $this->teamProfile->setParameterHalf(0);
        $this->teamProfile->setParameterSafe(0);
        $this->teamProfile->setInGroup(0);

        $installResult = $this->teamProfile->install();

        track_stats(); // Отслеживаем производительность

        if($installResult instanceof ErrorPoint){
            SQL::getInstance()->rollback();
            return $actionResult;
        }
         

        if($addOnStudyPoints > GlobalParameters::MAX_TEAM){
            SQL::getInstance()->rollback();
            return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Друзей в команду набрано больше чем допустимо", ErrorPoint::TYPE_USER);
        }

        $needRandomFootballers = GlobalParameters::MAX_TEAM - $addOnStudyPoints;

        track_stats(); // Отслеживаем производительность

        if($needRandomFootballers){

            $prototypes = FootballerSatellite::getRandomPrototypes($needRandomFootballers);
            if($prototypes instanceof ErrorPoint){
                SQL::getInstance()->rollback();
                return $prototypes;
            }

            foreach ($prototypes as $footballerPrototype) {
                $footballer = new Footballer();
                $actionResult = $footballer->add($footballerPrototype, $needRandomFootballers --);
                if($actionResult instanceof ErrorPoint){
                    SQL::getInstance()->rollback();
                    return $actionResult;
                }
                $this->teamProfile->addFootballerToStore($footballer);
            }
        }

        track_stats(); // Отслеживаем производительность

        $this->teamProfile->updateTeamParameters();

        $addOnStudyPoints += 5; // Дадим ещё немного очков, для заманухи

        $this->teamProfile->setStudyPoints($addOnStudyPoints);

        $installResult = $this->teamProfile->save();

        track_stats(); // Отслеживаем производительность

        if($installResult instanceof ErrorPoint){
            SQL::getInstance()->rollback();
            return $actionResult;
        }else{
            SQL::getInstance()->commit();
        }
 
/*        $getResult = $this->teamProfile->initById(UserParameters::getUserId());

        if($getResult instanceof ErrorPoint){
            SQL::getInstance()->rollback();
            return $actionResult;
        }
*/
        track_stats(); // Отслеживаем производительность

        $api = new VKapi(VK_API_SECRET, VK_API_ID, VK_MAILING_SPEED);
        $api->setStatus(UserParameters::getUserId(), sprintf(VK_APPLICATION_STATUS, $this->teamProfile->getTeamName()));

        track_stats(); // Отслеживаем производительность

        return $installResult;

    }

}
