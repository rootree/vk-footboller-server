<?php
/**
 * Created by IntelliJ IDEA.
 * User: Администратор
 * Date: 25.04.2010
 * Time: 14:40:22
 * To change this template use File | Settings | File Templates.
 */

class TeamSatellite {

    static public function cratePrizeDateLabel($vkIdTo){

        $sql_template =
                "INSERT INTO check_prizes (vk_id_from, vk_id_to, prize_date) VALUES (%d, %d, NOW())
ON DUPLICATE KEY UPDATE prize_date = NOW()";

        $sql = sprintf($sql_template,
            UserParameters::getUserId(),
            $vkIdTo
        );

        $SQLResult = SQL::getInstance()->query($sql);

        if($SQLResult instanceof ErrorPoint){
            return $SQLResult;
        }
    }

    static public function prizeStudyPoint($teamId){

        $sql_template =
                "UPDATE teams SET
  `stady_point` = `stady_point` + 1,
  `prize_stady_point` = prize_stady_point + 1
WHERE
    vk_id = %d";

        $sql = sprintf($sql_template,
            $teamId
        );

        $SQLResult = SQL::getInstance()->query($sql);

        if($SQLResult instanceof ErrorPoint){
            return $SQLResult;
        }

        $team = RAM::getInstance()->getTeamById($teamId);
        if(!empty($team)){
            RAM::getInstance()->changeTeamField($teamId, 'studyPoints', $team->getStudyPoints() + 1);
            RAM::getInstance()->changeTeamField($teamId, 'studyPointsViaPrize', $team->getStudyPointsViaPrize() + 1);
        }
    }

    static public function getPrizeStudyPoint($teamId){

        $teamInstance = RAM::getInstance()->getTeamById($teamId);
        if(!empty($teamInstance)){

            $team = new Team();
            $team->initFromRAM($teamInstance);

            return $team->getStudyPointsViaPrize();
        }

        $sql_template =
"SELECT prize_stady_point FROM teams
WHERE
    vk_id = %d";

        $sql = sprintf($sql_template,
            $teamId
        );

        $SQLResult = SQL::getInstance()->query($sql); 
        if($SQLResult instanceof ErrorPoint){
            return $SQLResult;
        }
 
        if($SQLResult->num_rows){ 
            $loadedTeam = $SQLResult->fetch_object();
            return $loadedTeam->prize_stady_point;
        } 
    }

    static public function resetPrizeStudyPoint($teamId){

        $sql_template =
"UPDATE teams SET
  `prize_stady_point` = 0
WHERE
    vk_id = %d";

        $sql = sprintf($sql_template,
            $teamId
        );

        $SQLResult = SQL::getInstance()->query($sql);

        if($SQLResult instanceof ErrorPoint){
            return $SQLResult;
        }

        $team = RAM::getInstance()->getTeamById($teamId);
        if(!empty($team)){
            RAM::getInstance()->changeTeamField($teamId, 'studyPointsViaPrize', 0);
        }

        $api = new VKapi(VK_API_SECRET, VK_API_ID, VK_MAILING_SPEED);
        $api->setCounter($teamId, 0);

    }

    static public function accrueDailyBonus($teamId, $dailyBonus){

        $sql_template =
                "UPDATE teams SET
  `daily_bonus` = %d,
  `money` = %d
WHERE
    vk_id = %d";

        $sql = sprintf($sql_template,
            date("d"),
            $dailyBonus,
            $teamId
        );

        $SQLResult = SQL::getInstance()->query($sql);

        if($SQLResult instanceof ErrorPoint){
            return $SQLResult;
        }

        $team = RAM::getInstance()->getTeamById($teamId);
        if(!empty($team)){
            RAM::getInstance()->changeTeamField($teamId, 'isNeedDailyBonus', false);
            RAM::getInstance()->changeTeamField($teamId, 'money', $dailyBonus);
        }
    }

    static public function updateTourNotify($teamId, $notifyStatus){

        $sql_template =
                "UPDATE teams SET
  `tour_notify` = %d
WHERE
    vk_id = %d";

        $sql = sprintf($sql_template,
            $notifyStatus,
            $teamId
        );

        $SQLResult = SQL::getInstance()->query($sql);

        if($SQLResult instanceof ErrorPoint){
            return $SQLResult;
        }

        $team = RAM::getInstance()->getTeamById($teamId);
        if(!empty($team)){
            RAM::getInstance()->changeTeamField($teamId, 'tourNotify', $notifyStatus);
        }
    }

    static public function isAvailableToPrize($teamId){

        $sql_template =
                "SELECT prize_date FROM check_prizes WHERE
    vk_id_from = %d AND
    vk_id_to = %d AND
    prize_date > (now() - INTERVAL 1 DAY)";

        $sql = sprintf($sql_template,
            UserParameters::getUserId(),
            $teamId
        );

        $SQLResult = SQL::getInstance()->query($sql);

        if($SQLResult instanceof ErrorPoint){
            return $SQLResult;
        }

        if($SQLResult->num_rows){
            return false;
        }else{
            return true;
        }
    }

    static public function markFriendAsFree($teamId, $free){

        $sql_template = "UPDATE teams SET in_team = %d WHERE vk_id = %d";

        $sql = sprintf($sql_template,
            intval(!$free),
            intval($teamId)
        );

        $SQLResult = SQL::getInstance()->query($sql);

        if($SQLResult instanceof ErrorPoint){
            return $SQLResult;
        }

        $team = RAM::getInstance()->getTeamById($teamId);
        if(!empty($team)){
            RAM::getInstance()->changeTeamField($teamId, 'inTeam', intval(!$free));
            $team = RAM::getInstance()->getTeamById($teamId);
        }
    }

    static public function increaseChooseRating($teamId){

        $sql_template = "UPDATE teams SET counter_choose = counter_choose + 1 WHERE vk_id = %d";

        $sql = sprintf($sql_template,
            intval($teamId)
        );

        $SQLResult = SQL::getInstance()->query($sql); 
        if($SQLResult instanceof ErrorPoint){
            return $SQLResult;
        }

        $team = RAM::getInstance()->getTeamById($teamId);
        if(!empty($team)){
            RAM::getInstance()->changeTeamField($teamId, 'counterChoose', $team->getCounterChoose() + 1);
        }

    }

    static public function isFreshFriend($teamId){

        $team = RAM::getInstance()->getTeamById($teamId);
        if(!empty($team)){
            if($team->getIsPrized() == 0){
                return true;
            }else{
                return false;
            }
        }

        $sql_template = "SELECT is_prized FROM teams WHERE vk_id = %d";

        $sql = sprintf($sql_template,
            $teamId
        );

        $SQLResult = SQL::getInstance()->query($sql);

        if($SQLResult instanceof ErrorPoint){
            return $SQLResult;
        }

        if($SQLResult->num_rows){

            $isFresh = $SQLResult->fetch_object();

            if($isFresh->is_prized == 0){
                return true;
            }else{
                return false;
            }
        }else{
            return true;
        }
    }

    static public function isTourWasFinished($tourType, $playOffId = 0 ){

        $sql_template =
                "SELECT play_off_id FROM tour_play_off WHERE
    vk_id = %d AND
    tour_type = %d AND
    play_off_id = %d AND
    finished = 0";

        $sql = sprintf($sql_template,
            UserParameters::getUserId(),
            $tourType,
            $playOffId
        );

        $SQLResult = SQL::getInstance()->query($sql);

        if($SQLResult instanceof ErrorPoint){
            return $SQLResult;
        }

        if($SQLResult->num_rows){
            return false;
        }else{
            return true;
        }

    }

    static public function eraseTourBonus($teamId){

        $sql_template =
                "UPDATE teams SET
  `tour_bonus` = 0,
  `tour_bonus_time` = 0
WHERE
    vk_id = %d";

        $sql = sprintf($sql_template,
            $teamId
        );

        $SQLResult = SQL::getInstance()->query($sql);
        if($SQLResult instanceof ErrorPoint){
            return $SQLResult;
        }

        $team = RAM::getInstance()->getTeamById($teamId);
        if(!empty($team)){
            RAM::getInstance()->changeTeamField($teamId, 'tourBonus', 0);
            RAM::getInstance()->changeTeamField($teamId, 'tourBonusTime', 0);
        }
    }

    static public function startTourBonus($teamId, $finishBonusAt){

        $sql_template =
                "UPDATE teams SET
  `tour_bonus_time` = '%s'
WHERE
    vk_id = %d";

        $sql = sprintf($sql_template,
            date("Y-m-d H:i:s", $finishBonusAt),
            $teamId
        );

        $SQLResult = SQL::getInstance()->query($sql);
        if($SQLResult instanceof ErrorPoint){
            return $SQLResult;
        }

        $team = RAM::getInstance()->getTeamById($teamId);
        if(!empty($team)){
            RAM::getInstance()->changeTeamField($teamId, 'tourBonusTime', $finishBonusAt);
        }
    }

}
