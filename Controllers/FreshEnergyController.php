<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 17:48:45
 */

class FreshEnergyController extends Controller implements IController{

    public function getResult(){
        $this->result['balance'] = array(
            "money"     => $this->teamProfile->getMoney(),
            "realMoney" => $this->teamProfile->getRealMoney(),
            "energy"    => $this->teamProfile->getCurrentEnergy()
        );
        return $this->result;
    }

    public function action(){

        $actionResult = NULL;

        $isInGame = $this->parameters->isInGame;

        if($isInGame){
            if(GlobalParameters::PRICE_FRESH_MONEY > $this->teamProfile->getMoney()){
                return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Недостаточно денежных средств", ErrorPoint::TYPE_USER);
            }
        }else{
            if(GlobalParameters::PRICE_FRESH_REAL_MONEY > $this->teamProfile->getRealMoney()){
                return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Недостаточно денежных средств", ErrorPoint::TYPE_USER);
            }
        }

        track_stats(); // Отслеживаем производительность

        $this->teamProfile->setEnergy($this->teamProfile->getEnergyMax());

        if($isInGame){
            $this->teamProfile->setMoney($this->teamProfile->getMoney() - GlobalParameters::PRICE_FRESH_MONEY);
        }else{
            $this->teamProfile->setRealMoney($this->teamProfile->getRealMoney() - GlobalParameters::PRICE_FRESH_REAL_MONEY);
        }

        $actionResult = $this->teamProfile->save();
  
        track_stats(); // Отслеживаем производительность

        return $actionResult; 
    }

}
