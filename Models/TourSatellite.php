<?php
/**
 * Created by IntelliJ IDEA.
 * User: Администратор
 * Date: 25.04.2010
 * Time: 14:40:22
 * To change this template use File | Settings | File Templates.
 */

class TourSatellite {
    
    static public function getTimerDate(){

        $data = file_get_contents(SYSTEM_LOGS . "/" . TOUR_TIMER_FILE);
        $date = json_decode($data);
        return $date;

    }

    static public function setTimerDate($startAt, $finishAt, $periodType = TOUR_NOTIFY_START){

        $data = new stdClass();
        
        $data->startAt = $startAt;
        $data->finishAt = $finishAt;
        $data->periodType = $periodType;

        file_put_contents(SYSTEM_LOGS . "/" . TOUR_TIMER_FILE, json_encode($data));

        RAM::getInstance()->setTourStart($startAt);
        RAM::getInstance()->setTourFinish($finishAt);
        RAM::getInstance()->setPeriodType($finishAt);

    }
    
}
