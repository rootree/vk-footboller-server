<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 18:10:07
 */

class LevelsGrid {

    private $levelGrid;

    private static $instance;

    function __construct(){
 
        $this->levelGrid[] = new LevelEntity(/*level*/0, /*nextExperiance*/ 0, /*baseEnergy*/0, /*studyPoints*/17);
        $this->levelGrid[] = new LevelEntity(1, 20, 100, 7);
        $this->levelGrid[] = new LevelEntity(2, 100, 115, 9);
        $this->levelGrid[] = new LevelEntity(3, 300, 130, 13);
        $this->levelGrid[] = new LevelEntity(4, 700, 140, 15);
        $this->levelGrid[] = new LevelEntity(5, 1500, 150, 18);
        $this->levelGrid[] = new LevelEntity(6, 4000, 160, 22);
        $this->levelGrid[] = new LevelEntity(7, 8000, 180, 30);
        $this->levelGrid[] = new LevelEntity(8, 12000, 200, 35);
        $this->levelGrid[] = new LevelEntity(9, 18000, 225, 37);
        $this->levelGrid[] = new LevelEntity(10, 25000, 235, 40);
        $this->levelGrid[] = new LevelEntity(11, 33000, 250, 50);
        $this->levelGrid[] = new LevelEntity(12, 40000, 270, 70);
        $this->levelGrid[] = new LevelEntity(13, 48000, 290, 85);
        $this->levelGrid[] = new LevelEntity(14, 55000, 330, 99);
        $this->levelGrid[] = new LevelEntity(15, 60000, 370, 110);
        $this->levelGrid[] = new LevelEntity(16, 65000, 400, 130);
        $this->levelGrid[] = new LevelEntity(17, 70000, 450, 150);
        $this->levelGrid[] = new LevelEntity(18, 77000, 500, 170);
        $this->levelGrid[] = new LevelEntity(19, 85000, 600, 190);
        $this->levelGrid[] = new LevelEntity(20, 100000, 800, 220);
        $this->levelGrid[] = new LevelEntity(21, 120000, 1000, 250);
        $this->levelGrid[] = new LevelEntity(22, 150000, 1200, 300);
        $this->levelGrid[] = new LevelEntity(23, 170000, 1300, 350);
        $this->levelGrid[] = new LevelEntity(24, 190000, 1500, 500);
        $this->levelGrid[] = new LevelEntity(25, 220000, 1800, 800);
        $this->levelGrid[] = new LevelEntity(26, 260000, 2000, 1200);
        $this->levelGrid[] = new LevelEntity(27, 300000, 2340, 1500);
        $this->levelGrid[] = new LevelEntity(28, 350000, 2600, 2000);
        $this->levelGrid[] = new LevelEntity(29, 390000, 3000, 2500);
        $this->levelGrid[] = new LevelEntity(30, 430000, 3300, 3000);
        $this->levelGrid[] = new LevelEntity(31, 490000, 4000, 4000);
        $this->levelGrid[] = new LevelEntity(32, 600000, 4500, 5000);
        $this->levelGrid[] = new LevelEntity(33, 660000, 5000, 6000);
        $this->levelGrid[] = new LevelEntity(34, 750000, 5600, 7000);
        $this->levelGrid[] = new LevelEntity(35, 850000, 6000, 10000);
        $this->levelGrid[] = new LevelEntity(36, 1000000, 6700, 12000);
        $this->levelGrid[] = new LevelEntity(37, 110000, 7500, 15000);
        $this->levelGrid[] = new LevelEntity(38, 130000, 8000, 20000);
        $this->levelGrid[] = new LevelEntity(39, 150000, 8600, 22000);
        $this->levelGrid[] = new LevelEntity(40, 180000, 9300, 23000);
        $this->levelGrid[] = new LevelEntity(41, 210000, 9900, 24000);
        $this->levelGrid[] = new LevelEntity(42, 250000, 12000, 25000);
        $this->levelGrid[] = new LevelEntity(43, 300000, 13000, 26000);
        $this->levelGrid[] = new LevelEntity(44, 500000, 15000, 27000);
        $this->levelGrid[] = new LevelEntity(45, 700000, 17000, 28000);
        $this->levelGrid[] = new LevelEntity(46, 900000, 19000, 29000);
        $this->levelGrid[] = new LevelEntity(47, 1200000, 23000, 30000);
        $this->levelGrid[] = new LevelEntity(48, 1500000, 25000, 32000);
        $this->levelGrid[] = new LevelEntity(49, 1800000, 27000, 35000);
        $this->levelGrid[] = new LevelEntity(50, 2000000, 30000, 40000);

    }

    public static function getInstance(){
        if(!LevelsGrid::$instance){
            LevelsGrid::$instance = new LevelsGrid();
        }
        return LevelsGrid::$instance;
    }

    public function getNextLevelExp($level){
        $levelEntity = $this->levelGrid[$level];
        return $levelEntity->getNextLevelExp();
    }

    public function getStudyPoints($level){
        $levelEntity = $this->levelGrid[$level];
        return $levelEntity->getStudyPoints();
    }

    public function getBaseEnergy($level){
        if(isset($this->levelGrid[$level])){
            $levelEntity = $this->levelGrid[$level];
            return $levelEntity->getBaseEnergy();
        }else{ 
            handlerError(E_USER_WARNING, "Попытка получить несуществуюзего уровня: ". $level, __FILE__, __LINE__);
            return 100;
        }
    }

    public function levelExist($level) {
        return isset($this->levelGrid[$level]);
    }
}

?>