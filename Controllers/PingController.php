<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 17:47:22
 */

class PingController extends Controller implements IController {

    public function getResult(){ 
        return $this->result;
    }

    public function action(){

        $this->result["isInstalled"] = $this->teamProfile->getIsInstalled();

        track_stats(); // Отслеживаем производительность

        $startAt = RAM::getInstance()->getTourStart();
        $finishAt = RAM::getInstance()->getTourFinish();

        track_stats(); // Отслеживаем производительность

        if(empty($startAt) || empty($finishAt)){

            $tourTimer = TourSatellite::getTimerDate();

            $startAt = $tourTimer->startAt;
            $finishAt = $tourTimer->finishAt;

            RAM::getInstance()->setTourStart($startAt);
            RAM::getInstance()->setTourFinish($finishAt);

        }

        track_stats(); // Отслеживаем производительность

        $this->result["tourStartAt"] = $startAt;
        $this->result["tourFinishedAt"] = $finishAt;
        $this->result["serverTime"] = time();

        if($this->teamProfile->getIsInstalled()){

            track_stats(); // Отслеживаем производительность
 
            $energyTimer = RAM::getInstance()->getEnergyLastUpdate();
            if(empty($energyTimer)){
                $energyTimer = filemtime(SYSTEM_LOGS . "/cron.updateEnergy.log"); // microtime
                RAM::getInstance()->setEnergyLastUpdate($energyTimer);
            } 
            $this->result['energyTimer'] = $energyTimer;

            track_stats(); // Отслеживаем производительность

            if($this->teamProfile->isNeedDailyBonus()){
                $dailyBonus = $this->teamProfile->getTotalStadiumBonus();
                $this->teamProfile->setMoney($this->teamProfile->getMoney() + $dailyBonus);
                $actionResult = TeamSatellite::accrueDailyBonus(UserParameters::getUserId(), $this->teamProfile->getMoney());
                if($actionResult instanceof ErrorPoint){ 
                    return $actionResult;
                }
            }

            track_stats(); // Отслеживаем производительность
 
            if($this->teamProfile->isNewTour() && $this->teamProfile->getTourBonus() != 0 && $this->teamProfile->getTourBonusTime() == 0){

                $finishBonusAt = time() + (1 * 24 * 60 * 60); 
                $actionResult = TeamSatellite::startTourBonus(UserParameters::getUserId(), $finishBonusAt);
                if($actionResult instanceof ErrorPoint){
                    return $actionResult;
                }

                $this->teamProfile->setTourBonusTime($finishBonusAt);
            }

            track_stats(); // Отслеживаем производительность

            $this->result["teamInfo"] = JSONPrepare::team($this->teamProfile);

            track_stats(); // Отслеживаем производительность

            // Это надо обновить после отдачи профайла

            if($this->teamProfile->getTourNotify() == TOUR_NOTIFY_START || $this->teamProfile->getTourNotify() == TOUR_NOTIFY_NEW){
                $actionResult = TeamSatellite::updateTourNotify(UserParameters::getUserId(), $this->teamProfile->getTourNotify() - 2);
                if($actionResult instanceof ErrorPoint){
                    return $actionResult;
                }
            }

            track_stats(); // Отслеживаем производительность

            if($this->teamProfile->isNewTour() && $this->teamProfile->getTourBonus() != 0 && $this->teamProfile->getTourBonusTime() > 0 && $this->teamProfile->getTourBonusTime() < time()){
                $actionResult = TeamSatellite::eraseTourBonus(UserParameters::getUserId());
                if($actionResult instanceof ErrorPoint){
                    return $actionResult;
                }
                $this->teamProfile->setTourBonus(0);
                $this->teamProfile->setTourBonusTime(0);
            }

            track_stats(); // Отслеживаем производительность
            
            if($this->teamProfile->getStudyPointsViaPrize() > 0){
                $actionResult = TeamSatellite::resetPrizeStudyPoint(UserParameters::getUserId());
                if($actionResult instanceof ErrorPoint){
                    return $actionResult;
                }
            }
            //Utils::forDebug($this->teamProfile);

        }
    }
}

?>