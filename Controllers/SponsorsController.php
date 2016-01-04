<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 17:48:10
 */

class SponsorsController extends Controller implements IController{

    public function getResult(){
        $this->result['isOk'] = 1;
        return $this->result;
    }

    public function action(){

        SQL::getInstance()->autocommit(false);

        $actionResult = SponsorSatellite::erase();
/*
        $sponsorsStoreInRAM = RAM::getInstance()->getObjectsForTeam(UserParameters::getUserId(), RAM::RAM_TYPE_SPONSOR);
        foreach ($sponsorsStoreInRAM as $sponsorInstance){
            RAM::getInstance()->deleteSponsor($sponsorInstance->getId(), UserParameters::getUserId());
        }
*/
        track_stats(); // Отслеживаем производительность

        $energyRate = 1;
        $sponsorsStore = array();
        $actionResult = NULL;
 
        if(!is_object($this->parameters) && !is_array($this->parameters)){
            
            $this->parameters = str_replace('\\\\"', '', $this->parameters);
            $this->parameters = str_replace(']\\"', ']', $this->parameters);
            $this->parameters = str_replace('\\"[', '[', $this->parameters);

            $this->parameters = json_decode($this->parameters);
        }

        track_stats(); // Отслеживаем производительность

        if (!(Utils::isEmpty($this->parameters)) && !($actionResult instanceof ErrorPoint)) {

            $sponsorDBResult = SponsorSatellite::getFromStoreByIds($this->parameters); 
            $sponsorCount = count($sponsorDBResult);

            track_stats(); // Отслеживаем производительность

            if($sponsorDBResult instanceof ErrorPoint){
                $actionResult = $sponsorDBResult;
                
            }elseif($sponsorCount){
 
                if($sponsorCount > GlobalParameters::SPONSORS_LIMIT){
                    $actionResult = new ErrorPoint(ErrorPoint::CODE_LOGIC, ("Количество спонсоров превышет допустимый предел. Получено " .
                            $sponsorCount . " спонсоров "), ErrorPoint::TYPE_SYSTEM);
                }else{

                    track_stats(); // Отслеживаем производительность

                    foreach ($sponsorDBResult as $sponsor){

                        $sponsorInstance = new Sponsor();
                        $sponsorInstance->initFromParameters($sponsor);

                        if($sponsorInstance->getRequiredLevel() > $this->teamProfile->getLevel()){
                            $actionResult = new ErrorPoint(ErrorPoint::CODE_SECURITY, "Спонсор не доступен по уровню. Уровень спонсора: " . $sponsorInstance->getRequiredLevel() .
                                    ", а у вас " . intval($this->teamProfile->getLevel()), ErrorPoint::TYPE_USER);
                            break;
                        }

                        $actionResult = $sponsorInstance->save();
                        if($actionResult instanceof ErrorPoint){
                            break;
                        }

                        $energyRate *= $sponsorInstance->getEnergy();
                        $sponsorsStore[] = $sponsorInstance; 
                    }

                    track_stats(); // Отслеживаем производительность

                }
            }
        }

        if($actionResult instanceof ErrorPoint){
            SQL::getInstance()->rollback();
            return $actionResult;
        }

        if($energyRate < 1){
            $energyRate = 1;
        }

        track_stats(); // Отслеживаем производительность

        $this->teamProfile->setSponsorRate($energyRate);
        $this->teamProfile->setMaxEnergy($energyRate * LevelsGrid::getInstance()->getBaseEnergy($this->teamProfile->getLevel()));
        $this->teamProfile->bindSponsors($sponsorsStore);

        $actionResult = $this->teamProfile->save();

        if($actionResult instanceof ErrorPoint){
            SQL::getInstance()->rollback();
        }else{
            SQL::getInstance()->commit();
        }

        track_stats(); // Отслеживаем производительность
        
        return $actionResult;

    }
}
