<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 23.06.2010
 * Time: 17:42:27
 */

class SponsorSatellite {

   public static function getFromStoreByIds($ids){

       if(empty($ids)){
            return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Переданы пустые ID для спонсоров", ErrorPoint::TYPE_SYSTEM);   
       }
 
       $sponsorsStore = array();
       foreach ($ids as $IdVar => $Id) {

            $sponsor = RAM::getInstance()->getSponsorPrototypeById($Id); 
            if(empty($sponsor)){

                $sponsor = SponsorSatellite::getSponsorPrototype($Id);
                if($sponsor instanceof ErrorPoint){
                    return $sponsor;
                }
                RAM::getInstance()->setSponsorPrototype($sponsor);
            }
            
            $sponsorsStore[] = $sponsor; 
       }
       return $sponsorsStore;
 
   }

    public static function getSponsorPrototype($sponsorId){
 
        $sql_template = "SELECT * FROM item_sponsors WHERE id = %d";
        $sql = sprintf($sql_template,
            $sponsorId
        );

        $SQLresult = SQL::getInstance()->query($sql);

        if($SQLresult instanceof ErrorPoint){
            return $SQLresult;
        }

        if(!$SQLresult->num_rows){
            return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Не найден спонсор в хранилище (#SQL:" . $sql. ")", ErrorPoint::TYPE_SYSTEM);
        }else{
            return $SQLresult->fetch_object();
        }

    }

    public static function initForTeam(& $team){

        $sponsorsStore = array();
        $sponsorRate = 1;

        $sponsorsStore = RAM::getInstance()->getObjectsForTeam($team->getSocialUserId(), RAM::RAM_TYPE_SPONSOR);
 
        if(count($sponsorsStore) != $team->getSponsorsCount() || $team->getSponsorsCount() == 0){//] || GlobalParameters::$IS_FAKE_ENTER
           // || GlobalParameters::MODER_ID == $team->getSocialUserId()){

            $sponsorsStore = array();

            $sql_template =
"SELECT
    item_sponsors.id,
    item_sponsors.energy,
    item_sponsors.required_level
FROM sponsors
LEFT JOIN item_sponsors ON item_sponsors.id = sponsors.sponsor_id
WHERE vk_id = '%s'";
 
            $sql = sprintf($sql_template,
                $team->getSocialUserId()
            );
            $SQLresult = SQL::getInstance()->query($sql);

            if($SQLresult instanceof ErrorPoint){
                return $SQLresult;
            }

            $team->setSponsorsCount($SQLresult->num_rows, true);
    
            if($SQLresult->num_rows){
                $counter = 0;
                while ($sponsor = $SQLresult->fetch_object()){
                    $sponsorInstance = new Sponsor();
                    $sponsorInstance->initFromParameters($sponsor);
                    $sponsorRate *= $sponsorInstance->getEnergy();
                    $sponsorsStore[$sponsorInstance->getId()] = $sponsorInstance;

                    RAM::getInstance()->setSponsor($sponsorInstance, $team->getSocialUserId(), $counter);
                    $counter ++;
                }

                if(count($sponsorsStore) > GlobalParameters::SPONSORS_LIMIT){
                    return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Количество спонсоров превышет допустимого значения " . count($sponsorsStore) . " - " . $SQLresult->num_rows, ErrorPoint::TYPE_SYSTEM);
                }
                
                $team->setSponsorRate($sponsorRate);
                $team->setMaxEnergy($sponsorRate * LevelsGrid::getInstance()->getBaseEnergy($team->getLevel()));

            }

        }else{

            foreach ($sponsorsStore as $sponsorInstance){
                $sponsorRate *= $sponsorInstance->getEnergy();
            }
        }
 
        return $sponsorsStore;
    }


    public static function erase() {

        $sql_template = "DELETE FROM sponsors WHERE vk_id = %d";
        $sql = sprintf($sql_template,
            UserParameters::getUserId()
        );
        $SQLresult = SQL::getInstance()->query($sql);
        return $SQLresult;
    }


}
