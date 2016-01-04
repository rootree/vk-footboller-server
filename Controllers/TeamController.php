<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 17:52:40
 */

class TeamController extends Controller implements IController{

    public function getResult(){
        $this->result['studyPoints'] = $this->teamProfile->getStudyPoints();
        $this->result['teamParameters'] = array(
            "Forward" => $this->teamProfile->getParameterForward(),
            "Safe" => $this->teamProfile->getParameterSafe(),
            "Half" => $this->teamProfile->getParameterHalf(),
        ); 
        return $this->result;
    }
     
    public function action(){

        track_stats(); // Отслеживаем производительность

        $levelSum = 0;
        if(count($this->parameters)){
            foreach ($this->parameters as $footballer_id => $footballerParameters) {
                $levelSum += intval($footballerParameters->level);
            }
        }

        $currentSumLevelCount = $this->teamProfile->getFootballerSumLevel();

        track_stats(); // Отслеживаем производительность

        $spentStudyPoints = ($levelSum - $currentSumLevelCount);

        if($spentStudyPoints > $this->teamProfile->getStudyPoints() * 2){
			$errorMessage = "Вы потратили слишком много очков обучения. У вас есть " . $this->teamProfile->getStudyPoints() . 
                    ", а потрачено " . $spentStudyPoints . ". Действие отменено";
 
            return new ErrorPoint(ErrorPoint::CODE_SECURITY, $errorMessage, ErrorPoint::TYPE_USER);
        }
 
        SQL::getInstance()->autocommit(false);

        track_stats(); // Отслеживаем производительность

        $footballersInDB = $this->teamProfile->getFootballers();
        $isExistsGoalKeeper = false;

        foreach ($footballersInDB as & $footballerParameters) {

            $footballerId = $footballerParameters->getId();

            if(!isset($this->parameters->$footballerId)){
                continue;
                // SQL::getInstance()->rollback();
                // return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Футболист под номером " + $footballerId + " не найден", ErrorPoint::TYPE_USER);
            }

            $this->parameters->$footballerId->isActive = intval($this->parameters->$footballerId->isActive);
            $this->parameters->$footballerId->type     = intval($this->parameters->$footballerId->type);
            $this->parameters->$footballerId->level    = intval($this->parameters->$footballerId->level);

            if($this->parameters->$footballerId->level != $footballerParameters->getLevel() ||
                    $this->parameters->$footballerId->type != $footballerParameters->getType() ||
                    $this->parameters->$footballerId->isActive != $footballerParameters->getIsActive()
                ){
                
                $footballerParameters->setLevel(($this->parameters->$footballerId->level));
                $footballerParameters->setType(($this->parameters->$footballerId->type));
                $footballerParameters->setActive(($this->parameters->$footballerId->isActive));
                 
                $updateResult = $footballerParameters->update(); 
                if($updateResult instanceof ErrorPoint){
                    SQL::getInstance()->rollback();
                    return $updateResult;
                }
            }

            if($footballerParameters->getIsActive() && $footballerParameters->getType() == TYPE_FOOTBALLER_GOALKEEPER_CODE){
                $isExistsGoalKeeper = true;                    
            }
        }

        track_stats(); // Отслеживаем производительность


        if($isExistsGoalKeeper === false){
            SQL::getInstance()->rollback();
            return new ErrorPoint(ErrorPoint::CODE_LOGIC, "В основном составе команды обязательно должен присутствовать вратарь", ErrorPoint::TYPE_USER);
        }

        if($this->teamProfile->getActiveCount() > GlobalParameters::MAX_TEAM){
            SQL::getInstance()->rollback();
            return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Ваша команда имеет неправильный состав игроков (Активных игроков: " . $this->teamProfile->getActiveCount() .
                    ")", ErrorPoint::TYPE_SYSTEM);   
        }

        track_stats(); // Отслеживаем производительность

        $this->teamProfile->updateTeamParameters();

        track_stats(); // Отслеживаем производительность

        $spentStudyPoints = ($spentStudyPoints < 0) ? 0 : $spentStudyPoints;
        $this->teamProfile->setStudyPoints($this->teamProfile->getStudyPoints() - $spentStudyPoints);

        $actionResult = $this->teamProfile->save();

        track_stats(); // Отслеживаем производительность

        if($actionResult instanceof ErrorPoint){
            SQL::getInstance()->rollback();
        }else{
            SQL::getInstance()->commit();
        }

        return $actionResult;

    }

}
