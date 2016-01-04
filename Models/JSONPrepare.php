<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 24.06.2010
 * Time: 16:36:18
 */

class JSONPrepare {

    public static function team(Team $team) {

        $forJSON = array(
            "level"        => $team->getLevel(),
            "experience"   => $team->getExperience(),
            "money"        => $team->getMoney(),
            "realMoney"    => $team->getRealMoney(),
            "studyPoints"  => $team->getStudyPoints(),
            "energy"       => $team->getCurrentEnergy(),
            "energyMax"    => floor($team->getEnergyMax()),
            "trainerId"    => $team->getTrainerId(),
            "teamName"     => $team->getTeamName(),
            "teamLogoId"   => $team->getTeamLogoId(),
            "paramForward" => $team->getParameterForward(),
            "paramHalf"    => $team->getParameterHalf(),
            "paramSafe"    => $team->getParameterSafe(),
            "isInstalled"  => $team->getIsInstalled(),
            "socialUserId" => $team->getSocialUserId(),
            "userPhoto"    => $team->getUserPhoto(),
            "userName"     => $team->getUserName(),
            "isInTeam"     => $team->getIsInTeam(),
            "studyPointsViaPrize"     => $team->getStudyPointsViaPrize(),
            "inGroup"     => $team->getInGroup(),
            "counterChoose"   => $team->getCounterChoose(),
            "counterWon"      => $team->getCounterWon(),
            "counterLose"     => $team->getCounterLose(),
            "stadiumId"       => $team->getStadiumId(),
            "needDailyBonus"  => $team->isNeedDailyBonus(),
            "placeCountry"    => $team->getPlaceCountry(),
            "placeCity"       => $team->getPlaceCity(),
            "placeUniversity" => $team->getPlaceUniversity(),
            "placeVK"         => $team->getPlaceVK(),
            "tourIIIcounter"  => $team->getTourIII(),

            "tourPlaceCountry"    => $team->getTourPlaceCountry(),
            "tourPlaceCity"       => $team->getTourPlaceCity(),
            "tourPlaceUniversity" => $team->getTourPlaceUniversity(),
            "tourPlaceVK"         => $team->getTourPlaceVK(),
 
            "tourNotify"    => $team->getTourNotify(),

            "tourBonus"     => $team->getTourBonus(),
            "tourBonusTime" => $team->getTourBonusTime(),

            "counterChoose" => $team->getCounterChoose() ,
            "counterWon"    => $team->getCounterWon() ,
            "counterLose"   => $team->getCounterLose(),
            "totalPlace"   => $team->getTotalPlace(),
            "place"         => isset($team->place) ? $team->place : 99,
        );


        if(count($team->getFootballers())){
            $forJSON["footballers"] = JSONPrepare::footballers($team->getFootballers());
        }

        if(count($team->getSponsors())){
            $forJSON["sponsors"] = JSONPrepare::sponsors($team->getSponsors());
        }

        return $forJSON;
    }

    public static function footballers($footballers) {
        $forJSON = array();

        foreach ($footballers as $footballerInstance) {
            $footballer = JSONPrepare::footballer($footballerInstance);
            $forJSON[] = $footballer;
        }

        return $forJSON;
    }

    public static function footballer(Footballer $footballerInstance) {

        $footballer = array(
            "id"               => $footballerInstance->getId(),
            "footballerName"   => $footballerInstance->getFootballerName(),
            "level"            => $footballerInstance->getLevel(),
            "type"             => $footballerInstance->getType(),
            "isFriend"         => $footballerInstance->getIsFriend(),
            "isActive"         => $footballerInstance->getIsActive(),
            "photoForFriend"   => $footballerInstance->getPhotoForFriend(), 
            "year"             => $footballerInstance->getYear(),
            "country"          => $footballerInstance->getCountry(),
            "team_name"        => $footballerInstance->getTeamName(),
            "favorite"         => $footballerInstance->getIsSuper(),
            "healthDown"         => $footballerInstance->getHealthDown()
    
        );

        return $footballer;
    }

    public static function sponsors($sponsors) {
        $forJSON = array();

        if(is_array($sponsors)){
            foreach ($sponsors as $sponsorInstance) {
                $sponsor = array(
                    "id"        => $sponsorInstance->getId(),
                    "energy"   => $sponsorInstance->getEnergy()
                );
                $forJSON[] = $sponsor;
            }
        }
 
        return $forJSON;
    }

}
