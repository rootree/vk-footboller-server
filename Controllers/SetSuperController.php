<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 17:48:45
 */

class SetSuperController extends Controller implements IController{

    public function getResult(){
        $this->result['balance'] = array(
            "money" => $this->teamProfile->getMoney(),
            "realMoney" => $this->teamProfile->getRealMoney()
        );
        return $this->result;
    }

    public function action(){

        track_stats(); // Отслеживаем производительность

        $actionResult = NULL;

        $superId = intval($this->parameters->footballerId);
        $footballerInstance = $this->teamProfile->getFootballerById($superId);

        if(empty($footballerInstance)){
            return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Выбранный футболист не найден в вашей команде", ErrorPoint::TYPE_USER);
        }

        $footballerPricePromotion = GlobalParameters::SUPER_PRICE;
        if($this->teamProfile->getRealMoney() < $footballerPricePromotion){
            return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Недостаточно средств для совершения этих действий", ErrorPoint::TYPE_USER);
        }

        if($footballerInstance->getIsSuper()){
            return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Данный футболист уже являться фаворитным", ErrorPoint::TYPE_USER);  
        }

        track_stats(); // Отслеживаем производительность

        SQL::getInstance()->autocommit(false);

        $footballerInstance->setAsSuper(true);
        $actionResult = $footballerInstance->update();
 
        if($actionResult instanceof ErrorPoint){
            SQL::getInstance()->rollback();
            return $actionResult;
        }

        track_stats(); // Отслеживаем производительность

        $this->teamProfile->setRealMoney($this->teamProfile->getRealMoney() - $footballerPricePromotion);
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
