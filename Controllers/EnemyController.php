<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 17:53:21
 */

class EnemyController extends Controller implements IController{

    public function getResult(){ 
        return $this->result;
    }
    
    public function action(){

        $this->result['teams'] = array();

        track_stats(); // Отслеживаем производительность

        $sql_template =
"SELECT
     *, unix_timestamp(tour_bonus_time) as tour_bonus_time 
 FROM teams
    WHERE 
%d < param_sum AND param_sum < %d AND MOD(vk_id, %d) = 0 AND able_to_choose = 1
 LIMIT 30";
 
        $sql = sprintf($sql_template,
            ($this->teamProfile->getParameterSum() - GlobalParameters::ENEMY_RANGE),
            ($this->teamProfile->getParameterSum() + GlobalParameters::ENEMY_RANGE), 
            rand(0, 29)
        );
 
        $teamResult = SQL::getInstance()->query($sql);
 
        if($teamResult instanceof ErrorPoint){
            return $teamResult;
        }

        track_stats(); // Отслеживаем производительность

        if($teamResult->num_rows){
            
            while($teamObject = $teamResult->fetch_object()){

                if(empty($teamObject->user_name) || $teamObject->vk_id == $this->teamProfile->getSocialUserId()){
                    continue;
                }

                $team = new Team();
                $team->initFromDB($teamObject);
                $chnase = Utils::detectChanceOfWin($this->teamProfile, $team);
                $teamInJSON = JSONPrepare::team($team);
                $teamInJSON["score"] = md5($chnase . $team->getSocialUserId() . SECRET_KEY);
 
                $this->result['teams'][] = $teamInJSON;
            }

            track_stats(); // Отслеживаем производительность
            
        }else{
 
            $sql_template =
"SELECT
    *, unix_timestamp(tour_bonus_time) as tour_bonus_time
FROM teams
    WHERE
%d < (param_sum) AND
(param_sum) < %d AND
MOD(vk_id, %d) = 0 and able_to_choose = 1
 LIMIT 30";

            $sql = sprintf($sql_template,
                ($this->teamProfile->getParameterSum() - GlobalParameters::ENEMY_RANGE * 3),
                ($this->teamProfile->getParameterSum() + GlobalParameters::ENEMY_RANGE * 3),
                rand(0, 29)
            );

            $teamResult = SQL::getInstance()->query($sql);

            if($teamResult instanceof ErrorPoint){
                return $teamResult;
            }

            track_stats(); // Отслеживаем производительность

            if($teamResult->num_rows){
                
                while($teamObject = $teamResult->fetch_object()){

                    if(empty($teamObject->user_name) || $teamObject->vk_id == $this->teamProfile->getSocialUserId()){
                        continue;
                    }

                    $team = new Team();
                    $team->initFromDB($teamObject);

                    $teamInJSON = JSONPrepare::team($team);
                    $chnase = Utils::detectChanceOfWin($this->teamProfile, $team);
                    //$teamInJSON["score"] = Utils::detectChanceOfWin($this->teamProfile, $team);
                    $teamInJSON["score"] = md5($chnase . $team->getSocialUserId() . SECRET_KEY);

                    $this->result['teams'][] = $teamInJSON;
                }

                track_stats(); // Отслеживаем производительность

            }
        }
    }
}
