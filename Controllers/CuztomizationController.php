<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 17:48:10
 */

class CuztomizationController extends Controller implements IController{

    public function getResult(){
        $this->result['isOk'] = 1;
        return $this->result;
    }

    public function action(){

        $teamLogoId = intval($this->parameters->teamLogoId);
        $teamTitle = trim($this->parameters->teamTitle);

        if(empty($teamLogoId) || empty($teamTitle)){
            $actionResult = new ErrorPoint(ErrorPoint::CODE_SECURITY, "Не указан логотип или название команды", ErrorPoint::TYPE_USER);
            return $actionResult;
        }

        track_stats(); // Отслеживаем производительность

        $this->teamProfile->setTeamName($teamTitle);
        $this->teamProfile->setTeamLogoId($teamLogoId);
        $actionResult = $this->teamProfile->save();

        if($actionResult instanceof ErrorPoint){
            SQL::getInstance()->rollback();
        }else{
            SQL::getInstance()->commit();
        }

        track_stats(); // Отслеживаем производительность

        $api = new VKapi(VK_API_SECRET, VK_API_ID, VK_MAILING_SPEED);
        $actionResult = $api->setStatus(UserParameters::getUserId(), sprintf(VK_APPLICATION_STATUS, $this->teamProfile->getTeamName()));

        track_stats(); // Отслеживаем производительность
        
        if($actionResult instanceof ErrorPoint){
            return $actionResult;
        }

        return $actionResult;

    }
}
