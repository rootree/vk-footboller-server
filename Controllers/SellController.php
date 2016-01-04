<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 17:48:45
 */

class SellController extends Controller implements IController{

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

        $sellId = intval($this->parameters->footballerId);
        $footballerInstance = $this->teamProfile->getFootballerById($sellId);

        if(($footballerInstance) === false){
            $errorMessage = "Анулирование контракта невозможно";
            // Utils::forDebug($errorMessage . " sellId : $sellId - UserId : " . $this->teamProfile->getSocialUserId());
            return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Анулирование контракта невозможно", ErrorPoint::TYPE_USER);
        }

        if($footballerInstance->getIsActive()){
            switch($footballerInstance->getType()){
                case TYPE_FOOTBALLER_FORWARD_CODE:
                    $this->teamProfile->setParameterForward($this->teamProfile->getParameterForward() - $footballerInstance->getLevel());
                    break;
                case TYPE_FOOTBALLER_SAFER_CODE:
                case TYPE_FOOTBALLER_GOALKEEPER_CODE:
                    $this->teamProfile->setParameterSafe($this->teamProfile->getParameterSafe() - $footballerInstance->getLevel());
                    break;
                case TYPE_FOOTBALLER_HALFSAFER_CODE:
                    $this->teamProfile->setParameterHalf($this->teamProfile->getParameterHalf() - $footballerInstance->getLevel());
                    break;
            }
        }

        track_stats(); // Отслеживаем производительность

        $this->result['teamParameters'] = array(
            "Forward" => $this->teamProfile->getParameterForward(),
            "Safe"    => $this->teamProfile->getParameterSafe(),
            "Half"    => $this->teamProfile->getParameterHalf(),
        );

        track_stats(); // Отслеживаем производительность

        SQL::getInstance()->autocommit(false);

        track_stats(); // Отслеживаем производительность

        $deleteResult = $this->teamProfile->deleteFootballerFromStore($footballerInstance);
        if($deleteResult instanceof ErrorPoint){
            SQL::getInstance()->rollback();
            return $deleteResult;
        }

        track_stats(); // Отслеживаем производительность

        if($footballerInstance->getIsFriend()){
            $markAsFreeResult = TeamSatellite::markFriendAsFree($footballerInstance->getId(), $free = true);
            if($markAsFreeResult instanceof ErrorPoint){
                SQL::getInstance()->rollback();
                return $markAsFreeResult;
            }
        }

        track_stats(); // Отслеживаем производительность

        $footballerPrice = FootballerSatellite::detectPrice($footballerInstance, $this->teamProfile->getStudyPointCost());
        $footballerPrice = ($footballerInstance->getIsSuper()) ? $footballerPrice * 3 : $footballerPrice;

        $footballerPrice *= 0.25;

        track_stats(); // Отслеживаем производительность

        $tourBonus = 1;
        if($this->teamProfile->isNewTour() && $this->teamProfile->getTourBonus() != 0 && $this->teamProfile->getTourBonusTime() > 0 && $this->teamProfile->getTourBonusTime() > time()){
            $tourBonus = $this->teamProfile->getTourBonus();
            $tourBonus -= 1;
            $tourBonus = 1 - $tourBonus;
        }

        if($tourBonus == 0){
            $tourBonus = 1;
        }

        track_stats(); // Отслеживаем производительность

        $footballerPrice = $footballerPrice * $tourBonus;

        $this->teamProfile->setMoney($this->teamProfile->getMoney() + $footballerPrice);
        $actionResult = $this->teamProfile->save();

        track_stats(); // Отслеживаем производительность

        if($footballerInstance->getIsFriend()){
            RAM::getInstance()->changeTeamField($this->teamProfile->getSocialUserId(), 'footballersFriendsCount', $this->teamProfile->getAllFootballersFriendsCount() - 1);
        }else{
            RAM::getInstance()->changeTeamField($this->teamProfile->getSocialUserId(), 'footballersCount', $this->teamProfile->getAllFootballersCount() - 1);
        }

        track_stats(); // Отслеживаем производительность

        if($actionResult instanceof ErrorPoint){
            SQL::getInstance()->rollback();
        }else{
            SQL::getInstance()->commit();
        }

        track_stats(); // Отслеживаем производительность
        
        return $actionResult;

    }

}
