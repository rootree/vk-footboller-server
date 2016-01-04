<?php
// +----------------------------------------------------------------------+
// | Обработка создания команды                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2009 - 2010                                            |
// +----------------------------------------------------------------------+
// | Authors: Ivan Chura <ivan.chura@gmail.com>                           |
// +----------------------------------------------------------------------+

/**
 * @version 1.0
 * @author Ivan Chura <ivan.chura@gmail.com>
 * @desc На самом деле получение списка уже занятых друзей
 */
class FriendTeamsController extends Controller implements IController {

    public function getResult(){
        return $this->result;
    }

    public function action(){
        return $this->getFriendsTeams();
    }

    private function getFriendsTeams(){

        $this->result['teams'] = array();
        $this->result['news'] = array();
        $this->result['rating'] = array();

        if($this->parameters->groupSourceId){
            logGroupSource($this->parameters->groupSourceId);
        }

        $needToUpgrade = false;

        $userCountry    = intval($this->parameters->userCountry) ;
        $userCity       = intval($this->parameters->userCity) ;
        $userUniversity = intval($this->parameters->userUniversity) ;

        track_stats(); // Отслеживаем производительность

        if($userCountry != $this->teamProfile->getUserCountry()){
            $this->teamProfile->setUserCountry($userCountry);
            $needToUpgrade = true;
        }
        if($userCity != $this->teamProfile->getUserCity()){
            $this->teamProfile->setUserCity($userCity);
            $needToUpgrade = true;
        }
        if($userUniversity != $this->teamProfile->getUserUniversity()){
            $this->teamProfile->setUserUniversity($userUniversity);
            $needToUpgrade = true;
        }

        if(isset($this->parameters->groupBonusNeeded) && $this->parameters->groupBonusNeeded == 1 && $this->teamProfile->getInGroup() == 0){
            $this->teamProfile->setInGroup(1);
            $this->teamProfile->setRealMoney($this->teamProfile->getRealMoney() + GlobalParameters::GROUP_BONUS_REAL);
            $needToUpgrade = true;
        }

        track_stats(); // Отслеживаем производительность

        if($needToUpgrade){
            $actionResult = $this->teamProfile->save();
            if($actionResult instanceof ErrorPoint){
                return $actionResult;
            }
        }

        if($this->parameters->uids){

            if(!is_object($this->parameters->uids)){
                $this->parameters->uids = str_replace('\\\\', '', $this->parameters->uids);
                $this->parameters->uids = str_replace('\\"', '', $this->parameters->uids);
                $this->parameters->uids = json_decode($this->parameters->uids);
            }

            track_stats(); // Отслеживаем производительность
      
            $sql_template =
"SELECT
    teams.team_name,
    teams.param_forward,
    teams.param_half,
    teams.param_safe,
    teams.user_photo,
    teams.user_name, 
    teams.team_logo_id, 
    teams.vk_id,
    teams.level
FROM teams
WHERE teams.vk_id IN (%s) order by teams.level desc,  teams.counter_won desc ";

            $sql = sprintf($sql_template,
                Utils::IdsSeparetedByComma($this->parameters->uids)
            );

            $SQLresult = SQL::getInstance()->query($sql);

            if($SQLresult instanceof ErrorPoint){
                return $SQLresult;
            }

            if($SQLresult->num_rows){

                $counterPlace = 1;
                while ($teamObject = $SQLresult->fetch_object()){


                    $team = new Team();
                    $team->initFromDB($teamObject, false);

                    if( $team->getLevel() == 0){
                        continue;
                    }

                    $team->place = $counterPlace;

                    $teamInJSON = JSONPrepare::team($team);
                    $counterPlace ++;
                    $this->result['teams'][] = $teamInJSON;

                  //  $this->result['teams'][] = $teamObject;
                }
            }
        }

 
        $this->result['news'] = RAM::getInstance()->getNews();

        if(count($this->result['news']) == 0){

            $sql_template = "SELECT news_sport.* FROM news_sport ORDER BY news_id";
            $sql = $sql_template;

            $SQLresult = SQL::getInstance()->query($sql);

            if($SQLresult instanceof ErrorPoint){
                return $SQLresult;
            }

            if($SQLresult->num_rows){
                $counter = 0;
                while ($newsObject = $SQLresult->fetch_object()){
                    $news = new NewsEntry($newsObject);
                    $news->id = $counter;
                    $this->result['news'][] = $news;
                    RAM::getInstance()->setNews($news);
                    $counter ++;
                }
            }
        }



        $leadTeams = RAM::getInstance()->getLeaders();
 
        if(count($leadTeams) == 0){
 
            $sql_template = "SELECT teams.* FROM teams WHERE total_place is not NULL ORDER BY total_place  LIMIT 6";
            $sql = $sql_template;

            $SQLresult = SQL::getInstance()->query($sql);

            if($SQLresult instanceof ErrorPoint){
                return $SQLresult;
            }

            if($SQLresult->num_rows){

                $counter = 0;
                while ($teamObject = $SQLresult->fetch_object()){

                    $team = new Team();
                    $team->initFromDB($teamObject);

                    RAM::getInstance()->setLeader($team, $counter);
                    $counter ++;

                    $teamInJSON = JSONPrepare::team($team);

                    $this->result['rating'][] = $teamInJSON;

                }
            } 
        }else{

            foreach ($leadTeams as $team){
                $teamInJSON = JSONPrepare::team($team);
                $this->result['rating'][] = $teamInJSON;
            }

        }
    }
}