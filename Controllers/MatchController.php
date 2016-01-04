<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 17:54:45
 */

class MatchController extends Controller implements IController{

    public function getResult(){

        $this->result['teamParameters'] = array(
            "Forward" => $this->teamProfile->getParameterForward(),
            "Safe" => $this->teamProfile->getParameterSafe(),
            "Half" => $this->teamProfile->getParameterHalf(),
        );

        $this->result["currentEnergy"] = $this->teamProfile->getCurrentEnergy();
        
        return $this->result;
    }

    public function action(){

        $detailId = 0;
        if(isset($this->parameters->detailId)){
            $detailId = intval($this->parameters->detailId);
        }
        $typeTour = 0;
        if(isset($this->parameters->typeTour)){
            $typeTour = intval($this->parameters->typeTour);
        }

        if($this->teamProfile->getCurrentEnergy() < GlobalParameters::ENERGY_PER_MATCH){
            return new ErrorPoint(ErrorPoint::CODE_SECURITY, "Для проведения новых матчей нужна энергия. На новый матч надо " .
                    GlobalParameters::ENERGY_PER_MATCH , ErrorPoint::TYPE_USER);
        }

        track_stats(); // Отслеживаем производительность

        $enemyTeam = new Team();
        $actionResult = $enemyTeam->initById($this->parameters->enemyTeamId);
        if($actionResult instanceof ErrorPoint){
            return $actionResult;
        }

        track_stats(); // Отслеживаем производительность

        $isTourRun = (isset($this->parameters->type) && $detailId && $typeTour);

        $this->result = JSONPrepare::footballers($enemyTeam->getFootballers());

        track_stats(); // Отслеживаем производительность

        $this->result["healthDown"] = 0;

        ;

        $score1 =  md5("1" . $this->parameters->enemyTeamId . SECRET_KEY);
        $score2 = md5("-1" . $this->parameters->enemyTeamId . SECRET_KEY);
        $score3 = md5("0" . $this->parameters->enemyTeamId . SECRET_KEY);

        $scoreTE = 0;
 
        $addonExperiance = GlobalParameters::EXPERIANCE_PER_MATCH_TIE;
        $addonMoney = GlobalParameters::MONEY_PER_MATCH_TIE;

        switch($this->parameters->score){
            case $score1:
                $scoreTE = 1;
                if(1 == rand(1, 100) && $this->teamProfile->getLevel() > 3){
                    $this->result["healthDown"] = $this->healthDownFootballer();
                }
                
                $addonExperiance = GlobalParameters::EXPERIANCE_PER_MATCH;
                $addonMoney = GlobalParameters::MONEY_PER_MATCH;
                $this->teamProfile->increaseWonRating();
                if(!$isTourRun){
                    $this->teamProfile->setTourIII($this->teamProfile->getTourIII() + 3);
                }
                break;
            case $score2:
                $scoreTE = -1;
                if(1 == rand(1, 30) && $this->teamProfile->getLevel() > 3){
                    $this->result["healthDown"] = $this->healthDownFootballer();
                }
                
                $addonExperiance = GlobalParameters::EXPERIANCE_PER_MATCH_LOSE;
                $addonMoney = GlobalParameters::MONEY_PER_MATCH_LOSE;
                $this->teamProfile->increaseLoseRating();

                break;
            case $score3:

                if(1 == rand(1, 60) && $this->teamProfile->getLevel() > 5){
                    $this->result["healthDown"] = $this->healthDownFootballer();
                }

                $addonExperiance = GlobalParameters::EXPERIANCE_PER_MATCH_TIE;
                $addonMoney = GlobalParameters::MONEY_PER_MATCH_TIE;
                $this->teamProfile->increaseTieRating();
                if(!$isTourRun){
                    $this->teamProfile->setTourIII($this->teamProfile->getTourIII() + 1);
                }
                break;
        }

        track_stats(); // Отслеживаем производительность

        $addonExperiance = $this->getAddOnXPorMoney($addonExperiance, $this->teamProfile->getLevel());
        $addonMoney = $this->getAddOnXPorMoney($addonMoney, $this->teamProfile->getLevel(), 10);

        $stadiumMoney = 0;
        if($this->teamProfile->getStadiumId() && 1 == rand(1, 10)){
            $stadiumMoney = $addonMoney * ($scoreTE + 2);
        }

        $stadiumMoney = ($stadiumMoney < 10000) ? $stadiumMoney : 0;;;
        $addonExperiance = ($addonExperiance < 10000) ? $addonExperiance : 0;
        $addonMoney = ($addonMoney < 10000) ? $addonMoney : 0;;

        $this->teamProfile->setEnergy($this->teamProfile->getCurrentEnergy() - GlobalParameters::ENERGY_PER_MATCH);
        $this->teamProfile->addExperience($addonExperiance);
        $this->teamProfile->setMoney($this->teamProfile->getMoney() + $addonMoney + $stadiumMoney);

        $this->result["stadiumBonus"] = $stadiumMoney;
        $this->result["addonEx"] = $addonExperiance;
        $this->result["addonMoney"] = $addonMoney;
        $this->result["currentEnergy"] = $this->teamProfile->getCurrentEnergy();
        $this->result["maxEnergy"] = $this->teamProfile->getEnergyMax();
        $this->result["score"] = $scoreTE;

        $this->result["bonus"] = null;
        $this->result["bonusTime"] = null;
        $this->result["totalBonus"] = null;

        track_stats(); // Отслеживаем производительность

        $markAsFreeResult = TeamSatellite::increaseChooseRating($this->parameters->enemyTeamId);
        if($markAsFreeResult instanceof ErrorPoint){
            SQL::getInstance()->rollback();
            return $markAsFreeResult;
        }
 
        track_stats(); // Отслеживаем производительность

        if($isTourRun){

            $sql = NULL;

            switch ($this->parameters->type){
                
                case 'groupsFight':

                    $sqlTemplate = "UPDATE tour_groups_details SET finished = 1 WHERE vk_id = %d AND group_details_id = %d";
                    $sql = sprintf($sqlTemplate,
                        UserParameters::getUserId(),
                        $detailId 
                    );
                     break;

                case 'playOffFight':

                    $sqlTemplate = "UPDATE tour_play_off SET finished = 1 WHERE vk_id = %d AND play_off_id = %d and tour_type = %d";
                    $sql = sprintf($sqlTemplate,
                        UserParameters::getUserId(),
                        $detailId,
                        $typeTour
                    );

                    break;
            }

            track_stats(); // Отслеживаем производительность

            if(!is_null($sql)){

                $SQLResult = SQL::getInstance()->query($sql);

                if($SQLResult instanceof ErrorPoint){
                    SQL::getInstance()->rollback();
                    return $SQLResult;
                }

                track_stats(); // Отслеживаем производительность
 

                if($this->parameters->type == 'playOffFight' && SQL::getInstance()->affected_rows == 1){

                    $tourFinished = TeamSatellite::isTourWasFinished($typeTour, $detailId);
                    if($tourFinished instanceof ErrorPoint){
                        return $tourFinished;
                    }

                    if($tourFinished){

                        $bonus = 1;
                        switch ($typeTour){
                            case TOUR_TYPE_VK:      $bonus = GoldCointsGrid::getInstance()->getBonusByPlace($typeTour, $this->teamProfile->getTourPlaceVK()); break;
                            case TOUR_TYPE_COUNTRY: $bonus = GoldCointsGrid::getInstance()->getBonusByPlace($typeTour, $this->teamProfile->getTourPlaceCountry());break;
                            case TOUR_TYPE_CITY:    $bonus = GoldCointsGrid::getInstance()->getBonusByPlace($typeTour, $this->teamProfile->getTourPlaceCity());break;
                            case TOUR_TYPE_UNI:     $bonus = GoldCointsGrid::getInstance()->getBonusByPlace($typeTour, $this->teamProfile->getTourPlaceUniversity());break;
                        }

                        $this->teamProfile->setTourBonus(($this->teamProfile->getTourBonus() > 0) ? $bonus * $this->teamProfile->getTourBonus() : $bonus); 

                        $this->result["bonus"] = $bonus;
                        //$this->result["bonusTime"] = $this->teamProfile->getTourBonusTime();

                        $this->result["totalBonus"] = $this->teamProfile->getTourBonus();
   
                    }
                }

                track_stats(); // Отслеживаем производительность
            }
        }

        if(is_infinite($this->teamProfile->getMoney())){
            $this->teamProfile->setMoney(10000);
        }

        $actionResult = $this->teamProfile->save();
        if($actionResult instanceof ErrorPoint){
            SQL::getInstance()->rollback();
            return $actionResult;
        }

        track_stats(); // Отслеживаем производительность

        logUserEnergy();

        track_stats(); // Отслеживаем производительность

        SQL::getInstance()->commit();

        track_stats(); // Отслеживаем производительность

    }

    private function getAddOnXPorMoney($base, $level, $coofi = 5){

        $total = $base;
        $aadon = ($total / 5);
        for($i = 1; $i < $level; $i ++ ){
            $aadon = ($total / $coofi)  ;
            $total += $aadon;
        }

        $total = floor($total + mt_rand(0, $aadon));
        return $total;
    }

    private function healthDownFootballer(){
 
        $foorballers = & $this->teamProfile->getFootballers();
        $healthFootballerSelected = false;
        $healthFootballer = false;

        $active = array();

        foreach($foorballers as & $foorballer){
            if($foorballer->getIsActive()){
                $active[] = $foorballer;
            }
        }

        $footballerIndex = rand(0, (count($active) - 1));

        $healthFootballer = $active[$footballerIndex];

        if(isset($healthFootballer)){
            $healthFootballer->setHealthDown(date("d"));
            $healthFootballer->setActive(0);

            $healthFootballer->update();


            $this->teamProfile->setIsAbleToChoose(0);
            $this->teamProfile->updateTeamParameters();

            return $healthFootballer->getId();
        }else{
            return 0;
        }
        
    }


}   