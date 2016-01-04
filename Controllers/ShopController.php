<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 17:48:45
 */

class ShopController extends Controller implements IController{

    public function getResult(){
        $this->result['balance'] = array(
            "money" => $this->teamProfile->getMoney(),
            "realMoney" => $this->teamProfile->getRealMoney()
        ); 
        return $this->result;
    }
    
    public function action(){
 
        $actionResult = NULL;

        track_stats(); // Отслеживаем производительность

        $shopItemInDB = FootballerSatellite::getFromStoreById($this->parameters->peopleId, $this->parameters->line);
       
        if($shopItemInDB instanceof ErrorPoint){
            return $shopItemInDB;
        }
 
        if(isset($this->parameters->line) && $this->parameters->line == TYPE_FOOTBALLER_TEAMLEAD_CODE){
            $structure = new TrainerPrototype($shopItemInDB);
        }else{
            $structure = new FootballerPrototype();
            $structure->init($shopItemInDB, $this->parameters);
        }

        track_stats(); // Отслеживаем производительность

        if($this->parameters->isInGame && $structure->getRequiredLevel() > $this->teamProfile->getLevel()){
            return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Для соверщения этой операции недостаточно уровня. У пользователя " .
                $this->teamProfile->getLevel(). " а надо " . $structure->getRequiredLevel(), ErrorPoint::TYPE_USER);
        }

        $tourBonus = 1;
        if($this->teamProfile->isNewTour() && $this->teamProfile->getTourBonus() != 0 && $this->teamProfile->getTourBonusTime() > 0 && $this->teamProfile->getTourBonusTime() > time()){
            $tourBonus = $this->teamProfile->getTourBonus();
            $tourBonus -= 1;
            $tourBonus = 1 - $tourBonus;
        }

        track_stats(); // Отслеживаем производительность

        if($tourBonus == 0){
            $tourBonus = 1;
        }
 
        if($this->parameters->isInGame){
            if($structure->getPrice() * $tourBonus > $this->teamProfile->getMoney()){
                return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Недостаточно денежных средств", ErrorPoint::TYPE_USER);
            } 
        }else{
            if($structure->getRealPrice() * $tourBonus > $this->teamProfile->getRealMoney()){
                return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Недостаточно денежных средств", ErrorPoint::TYPE_USER);
            }
        }

        track_stats(); // Отслеживаем производительность

        SQL::getInstance()->autocommit(false);
        
        if($structure instanceof TrainerPrototype){
            $this->teamProfile->setTrainer($structure->getId());

            track_stats(); // Отслеживаем производительность
            
        }else{

            if($this->teamProfile->getFootballerById($structure->getId())){
                return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Данный футболист уже в вашем клубе", ErrorPoint::TYPE_USER);    
            }

            track_stats(); // Отслеживаем производительность

            $footballerInstance = new Footballer();
            $actionResult = $footballerInstance->add($structure, $this->teamProfile->getActiveCount());
            if($actionResult instanceof ErrorPoint){
                SQL::getInstance()->rollback();
                return $actionResult;
            }

            track_stats(); // Отслеживаем производительность

            if($footballerInstance->getIsActive()){
                switch($footballerInstance->getType()){
                    case TYPE_FOOTBALLER_FORWARD_CODE:
                        $this->teamProfile->setParameterForward($this->teamProfile->getParameterForward() + $footballerInstance->getLevel());
                        break;
                    case TYPE_FOOTBALLER_SAFER_CODE:
                    case TYPE_FOOTBALLER_GOALKEEPER_CODE: 
                        $this->teamProfile->setParameterSafe($this->teamProfile->getParameterSafe() + $footballerInstance->getLevel());        
                        break;
                    case TYPE_FOOTBALLER_HALFSAFER_CODE:
                        $this->teamProfile->setParameterHalf($this->teamProfile->getParameterHalf() + $footballerInstance->getLevel());
                        break;
                }
            }
            $this->result['isActive'] = $footballerInstance->getIsActive();
            $this->result['teamParameters'] = array(
                "Forward" => $this->teamProfile->getParameterForward(),
                "Safe" => $this->teamProfile->getParameterSafe(),
                "Half" => $this->teamProfile->getParameterHalf(),
            );
            $this->teamProfile->addFootballerToStore($footballerInstance);

            track_stats(); // Отслеживаем производительность
        }

        if($this->parameters->isInGame){
            $this->teamProfile->setMoney($this->teamProfile->getMoney() - $structure->getPrice() * $tourBonus);
        }else{
            $this->teamProfile->setRealMoney($this->teamProfile->getRealMoney() - $structure->getRealPrice() * $tourBonus);
        }
        
        $actionResult = $this->teamProfile->save();

        track_stats(); // Отслеживаем производительность

        RAM::getInstance()->changeTeamField($this->teamProfile->getSocialUserId(), 'footballersCount', $this->teamProfile->getAllFootballersCount() + 1);

        if($actionResult instanceof ErrorPoint){
            SQL::getInstance()->rollback();
        }else{
            SQL::getInstance()->commit();
        }

        track_stats(); // Отслеживаем производительность
        
        return $actionResult;

    }

}
