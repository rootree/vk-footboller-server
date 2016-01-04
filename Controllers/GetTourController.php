<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 17:47:22
 */

class GetTourController extends Controller implements IController {

    private $userInGroups = false;
    private $userInPlayOff = false;

    public function getResult(){
        return $this->result;
    }

    public function action(){

        $groupType = intval($this->parameters->groupType);
        $placerId  = intval($this->parameters->placerId);

        if($groupType == 0 || ($groupType == TOUR_TYPE_VK && $placerId != 0)){
            return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Ошибка в программе", ErrorPoint::TYPE_USER);
        }

        track_stats(); // Отслеживаем производительность

        $sql_template = "SELECT vk_id, wins, ties, loses, score, group_number, in_play_off, tour_group_id FROM tour_groups WHERE was_closed = 0 and tour_type = %d and tour_placer_id = %d;";

        $sql = sprintf($sql_template,
            $groupType,
            $placerId
        );

        $SQLresult = SQL::getInstance()->query($sql);

        if($SQLresult instanceof ErrorPoint){
            return $SQLresult;
        }

        $group = array();

        track_stats(); // Отслеживаем производительность

        if($SQLresult->num_rows){

            while ($teamObject = $SQLresult->fetch_object()){

                $group[$teamObject->group_number][] = $teamObject;

                $team = new Team();
                $team->initById($teamObject->vk_id);
                $teamInJSON = JSONPrepare::team($team);
                $this->result['teams'][] = $teamInJSON;
 
                if($teamObject->vk_id == $this->teamProfile->getSocialUserId()){
                    $this->userInGroups = array($teamObject->tour_group_id, $teamObject->group_number) ;
                    if($teamObject->in_play_off){
                        $this->userInPlayOff = true;
                    }
                }
            }
        }

        track_stats(); // Отслеживаем производительность

        $this->result['group'] = $group;

        $playOff = array();
        $currentStep = 4;

        $playOff[$currentStep] = $this->getPlayOffByStep($currentStep, $groupType, $placerId); $currentStep --;
        $playOff[$currentStep] = $this->getPlayOffByStep($currentStep, $groupType, $placerId); $currentStep --;
        $playOff[$currentStep] = $this->getPlayOffByStep($currentStep, $groupType, $placerId); $currentStep --;
        $playOff[$currentStep] = $this->getPlayOffByStep($currentStep, $groupType, $placerId);

        $this->result['playOff'] = $playOff;

        track_stats(); // Отслеживаем производительность

        $this->result['groupSteps'] = array();

        if($this->userInGroups){

            $sql_template = "SELECT finished, goals_enemy, goals, result, vk_id_enemy, vk_id, group_details_id FROM tour_groups_details WHERE tour_group_id = %d and vk_id = %d ORDER BY group_details_id ;";

            $sql = sprintf($sql_template,
                $this->userInGroups[0],
                $this->teamProfile->getSocialUserId()
            );

            $SQLresult = SQL::getInstance()->query($sql);

            if($SQLresult instanceof ErrorPoint){
                return $SQLresult;
            }

            $finishedCounter = 0;

            if($SQLresult->num_rows){ 
                while ($groupStepObject = $SQLresult->fetch_object()){
                    $this->result['groupSteps'][$groupStepObject->group_details_id] = $groupStepObject;
                    $this->result['groupSteps'][$groupStepObject->group_details_id]->group_number = $this->userInGroups[1];
                    $finishedCounter += $groupStepObject->finished;
                }
            }

            if($finishedCounter == 3){
                $this->result['groupSteps'] = array();   
            }
        }

        track_stats(); // Отслеживаем производительность

        $this->result['playOffSteps'] = array();
        if($this->userInPlayOff){

            $sql_template = "SELECT vk_id_enemy, result, goals, goals_enemy, finished, play_off_step, play_off_id FROM tour_play_off WHERE vk_id = %d and tour_type = %d and tour_placer_id = %d ORDER BY play_off_id ;";

            $sql = sprintf($sql_template,
                $this->teamProfile->getSocialUserId(),
                $groupType,
                $placerId
            );

            $SQLresult = SQL::getInstance()->query($sql);

            if($SQLresult instanceof ErrorPoint){
                return $SQLresult;
            }

            if($SQLresult->num_rows){
                while ($playOffStepObject = $SQLresult->fetch_object()){
                    $this->result['playOffSteps'][$playOffStepObject->play_off_step] = $playOffStepObject; 
                }
            }
        }

        track_stats(); // Отслеживаем производительность

    }

    private function getPlayOffByStep($currentStep, $groupType, $placerId){

        $sql_template = "select * from tour_play_off where play_off_step = %d and tour_type = %d and tour_placer_id = %d ORDER BY play_off_id ;";
        $sql = sprintf($sql_template,
            $currentStep,
            $groupType,
            $placerId
        );

        $SQLresult = SQL::getInstance()->query($sql);

        if($SQLresult instanceof ErrorPoint){
            return $SQLresult;
        }

        $group = array();

        if($SQLresult->num_rows){

            while ($teamObject = $SQLresult->fetch_object()){

                if(isset($group[$teamObject->vk_id_enemy])){
                    $group[$teamObject->vk_id_enemy]['teamEnemy'] = $teamObject->vk_id;
                    continue;
                }

                if(!isset($group[$teamObject->vk_id])){
                    $group[$teamObject->vk_id] = array(
                        "team"      => $teamObject->vk_id,
                        "teamEnemy" => 0,
                        "goal"      => $teamObject->goals,
                        "goalEnemy" => $teamObject->goals_enemy
                    );
                }

            }
        }
        $forReturn = array();
        foreach ($group as $groupItem) {
            $forReturn[] = $groupItem;
        }
        return $forReturn;
    }
}

?>