<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 17:48:10
 */

class SendGiftController extends Controller implements IController{

    public function getResult(){
        $this->result['isOk'] = 1; 
        return $this->result;
    }

    public function action(){

        $friendId = intval($this->parameters->friendId);

        if(empty($friendId)){
            $actionResult = new ErrorPoint(ErrorPoint::CODE_SECURITY, "Получен не правильный номер друга", ErrorPoint::TYPE_USER);
            return $actionResult;
        }

        track_stats(); // Отслеживаем производительность

        $actionResult = TeamSatellite::isAvailableToPrize($friendId);
        if($actionResult instanceof ErrorPoint){ 
            return $actionResult;
        }

        if($actionResult === false){ 
            return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Выбранному пользователю вы уже дарили подарок. Теперь только завтра", ErrorPoint::TYPE_USER);
        }

        track_stats(); // Отслеживаем производительность

        SQL::getInstance()->autocommit(false);

        if($actionResult instanceof ErrorPoint){
            SQL::getInstance()->rollback();
            return $actionResult;
        }

        track_stats(); // Отслеживаем производительность

        $actionResult = TeamSatellite::cratePrizeDateLabel($friendId);
        if($actionResult instanceof ErrorPoint){
            SQL::getInstance()->rollback();
            return $actionResult;
        }

        track_stats(); // Отслеживаем производительность

        $actionResult = TeamSatellite::prizeStudyPoint($friendId);
        if($actionResult instanceof ErrorPoint){
            SQL::getInstance()->rollback();
            return $actionResult;
        }

        SQL::getInstance()->commit();

        track_stats(); // Отслеживаем производительность


        $api = new VKapi(VK_API_SECRET, VK_API_ID, VK_MAILING_SPEED);
        $api->setCounter($friendId, TeamSatellite::getPrizeStudyPoint($friendId));
 
        return $actionResult;

    }
}
