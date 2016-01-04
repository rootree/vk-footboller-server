<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 17:48:45
 */

class BuyStadiumController extends Controller implements IController{

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

        $stadiumInstance = new Stadium();
        $stadiumInstance->initById($this->parameters->stadiumId);

        track_stats(); // Отслеживаем производительность

        if($stadiumInstance instanceof ErrorPoint){
            return $stadiumInstance;
        }

        if($this->parameters->isInGame && $stadiumInstance->getRequiredLevel() > $this->teamProfile->getLevel()){
            return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Для соверщения этой операции недостаточно уровня. У Вас " .
                $this->teamProfile->getLevel(). ", а необходим " . $structure->getRequiredLevel(), ErrorPoint::TYPE_USER);
        }

        if($this->parameters->isInGame){
            if($stadiumInstance->getPrice() > $this->teamProfile->getMoney()){
                return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Недостаточно денежных средств", ErrorPoint::TYPE_USER);
            } 
        }else{
            if($stadiumInstance->getRealPrice() > $this->teamProfile->getRealMoney()){
                return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Недостаточно денежных средств", ErrorPoint::TYPE_USER);
            }
        }

        track_stats(); // Отслеживаем производительность

        SQL::getInstance()->autocommit(false);

        track_stats(); // Отслеживаем производительность

        if($this->teamProfile->getStadiumId() == $stadiumInstance->getId()){
            return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Вы уже приобрели выбранный стадион позже", ErrorPoint::TYPE_USER);
        }

        $this->teamProfile->setStadiumInstance($stadiumInstance);

        if($this->parameters->isInGame){
            $this->teamProfile->setMoney($this->teamProfile->getMoney() - $stadiumInstance->getPrice());
        }else{
            $this->teamProfile->setRealMoney($this->teamProfile->getRealMoney() - $stadiumInstance->getRealPrice());
        }
        
        $actionResult = $this->teamProfile->save();

        if($actionResult instanceof ErrorPoint){
            SQL::getInstance()->rollback();
        }else{
            SQL::getInstance()->commit();
        }

        track_stats(); // Отслеживаем производительность

        return $actionResult;

    }

}
