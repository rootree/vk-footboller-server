<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 18:10:07
 */

class GoldCointsGrid {

    private $cointsGrid;

    private static $instance;

    function __construct(){
 
        $this->cointsGrid[] = new GoldCoint(/*Бонус за первое место*/0, /*второе*/ 0, /*третье*/0);
        $this->cointsGrid[TOUR_TYPE_VK] = new GoldCoint(0.55, 0.60, 0.70);
        $this->cointsGrid[TOUR_TYPE_COUNTRY] = new GoldCoint(0.35, 0.40, 0.50);
        $this->cointsGrid[TOUR_TYPE_CITY] = new GoldCoint(0.20, 0.25, 0.30);
        $this->cointsGrid[TOUR_TYPE_UNI] = new GoldCoint(0.10, 0.15, 0.20);

    }
 
    public static function getInstance(){
        if(!GoldCointsGrid::$instance){
            GoldCointsGrid::$instance = new GoldCointsGrid();
        }
        return GoldCointsGrid::$instance;
    }

    public function getFirthBonus($tourType){
        $goldEntity = $this->cointsGrid[$tourType];
        return $goldEntity->getBonusForFirstPlace();
    }

    public function getSecondBonus($tourType){
        $goldEntity = $this->cointsGrid[$tourType];
        return $goldEntity->getBonusForSecondPlace();
    }

    public function getThirdBonus($tourType){
        $goldEntity = $this->cointsGrid[$tourType];
        return $goldEntity->getBonusForThirdPlace();
    }

    public function getBonusByPlace($tourType, $place){
        switch ($place) {
            case 1: return 1 + $this->getFirthBonus($tourType);
            case 2: return 1 + $this->getSecondBonus($tourType);
            case 3: return 1 + $this->getThirdBonus($tourType);
        } 
        return 1;
    }


}

?>