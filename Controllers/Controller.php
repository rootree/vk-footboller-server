<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 19:02:48
 */

class Controller {

    protected $currentError;

    protected $parameters;

    protected $teamProfile;

    protected $result;

    function __construct($parameters){

/*        if(GlobalParameters::getCommand() != COMMAND_PING){
            exit();
        }*/

        track_stats(); // Отслеживаем производительность

        logUserAction(); 
        $this->parameters = $parameters;
        $this->teamProfile = new Team();

        track_stats(); // Отслеживаем производительность

        $initResult = $this->teamProfile->initById(UserParameters::getUserId());

        if(0 && UserParameters::getUserId() == GlobalParameters::MODER_ID){

            if((preg_match("/id(\d+)/ms", $this->teamProfile->getTeamName(), $match)) ){
                $id = $match[1];
                UserParameters::setUserId($id);
                GlobalParameters::$IS_FAKE_ENTER = true;
                $initResult = $this->teamProfile->initById(UserParameters::getUserId());
            }
        }
 
        if($initResult instanceof ErrorPoint){
            $this->currentError = $initResult;
        }
  
    }

    public function getCurrentError(){
        if($this->currentError instanceof ErrorPoint){
            return $this->currentError;
        }else{
            return NULL;
        }
    }

    public function getResult(){
        return $this->result;
    }

    public function accountingStatistic($statistic){

        return;

        $SQL = '';

        if($statistic->mainMenu){
            foreach($statistic->mainMenu as $mainMenu => $count){
                $SQL .= "update statistic_main_menu set stat_count_click = (stat_count_click + " . intval($count). ") where stat_name = '$mainMenu';\r\n "; 
            }
        }

        if($statistic->shopItems){
            foreach($statistic->shopItems as $itemStatId => $itemStat){

                $SQL .= "
INSERT INTO statistic_shop (stat_id, stat_count_click, stat_count_hover) VALUES (" . $itemStatId . ", " . $itemStat->clk . ", " .  $itemStat->hvr . ")
ON DUPLICATE KEY UPDATE stat_count_click = stat_count_click + " . $itemStat->clk . ", stat_count_hover = stat_count_hover + " . $itemStat->hvr . " ;\r\n
";
 
            } 
        }
 
        SQL::getInstance()->query($SQL);

    }

}
