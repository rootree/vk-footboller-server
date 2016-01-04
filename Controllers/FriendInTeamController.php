<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 17:48:10
 */

class FriendInTeamController extends Controller implements IController{

    public function getResult(){
        $this->result['isOk'] = 1;
        $this->result['footballer'] = JSONPrepare::footballer($this->result['footballer']);
        return $this->result;
    }

    public function action(){

        $friendId = intval($this->parameters->friendId);

        if(empty($friendId)){
            $actionResult = new ErrorPoint(ErrorPoint::CODE_SECURITY, "Получен не правильный номер друга", ErrorPoint::TYPE_USER);
            return $actionResult;
        }

        track_stats(); // Отслеживаем производительность

        $alreadyExists = $this->teamProfile->getFootballerById($friendId);

        if($alreadyExists){
            return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Ваш друг уже играет за вас", ErrorPoint::TYPE_USER);
        }

        track_stats(); // Отслеживаем производительность

        $actionResult = FootballerSatellite::getFootballerOwner($friendId);
        if($actionResult instanceof ErrorPoint){
            return $actionResult;
        }

        if($actionResult !== false){
            return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Ваш друг уже числиться в другом футбольном клубе", ErrorPoint::TYPE_USER);
        }

        track_stats(); // Отслеживаем производительность

        SQL::getInstance()->autocommit(false);

        $friendFootballer = new Footballer();
        $actionResult = $friendFootballer->addFriend($friendId, NULL, $this->teamProfile->getActiveCount());
        if($actionResult instanceof ErrorPoint){
            SQL::getInstance()->rollback();
            return $actionResult;
        }

        track_stats(); // Отслеживаем производительность

        $addonPoints = 0;

        if(TeamSatellite::isFreshFriend($friendId)){
            // Даються очьку обучения как среднее по всех футболистов

            if(count($this->teamProfile->getFootballers()) != 0){ 
                $addonPoints = floor($this->teamProfile->getFootballerSumLevel() / count($this->teamProfile->getFootballers()));
                $this->teamProfile->setStudyPoints($this->teamProfile->getStudyPoints() + $addonPoints);
                $actionResult = $this->teamProfile->save();
                if($actionResult instanceof ErrorPoint){
                    SQL::getInstance()->rollback();
                    return $actionResult;
                }
            }

        }

        track_stats(); // Отслеживаем производительность

        $actionResult = Team::markTeamAsSelected($friendId);
        if($actionResult instanceof ErrorPoint){
            SQL::getInstance()->rollback();
            return $actionResult;
        }

        track_stats(); // Отслеживаем производительность

        RAM::getInstance()->changeTeamField($this->teamProfile->getSocialUserId(), 'footballersFriendsCount', $this->teamProfile->getAllFootballersFriendsCount() + 1);

        if($actionResult instanceof ErrorPoint){
            SQL::getInstance()->rollback();
        }else{
            SQL::getInstance()->commit();
            $this->result['footballer'] = $friendFootballer;
            $this->result['addonStadyPoints'] = $addonPoints;
            $this->result['totalStadyPoints'] = $this->teamProfile->getStudyPoints();
        }

        track_stats(); // Отслеживаем производительность
        
        return $actionResult;

    }
}
