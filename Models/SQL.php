<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 23.06.2010
 * Time: 16:24:15
 */

class SQL extends mysqli{

    private $counter;

    static $instance = NULL;

    public function __construct() {

        $this->counter = 0;
        
        track_stats(); // Отслеживаем производительность

        parent::__construct(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATA_BASE);

        track_stats(); // Отслеживаем производительность

        if ($this->connect_error) {
            return;
        }
/*        $this->query("SET NAMES 'utf8';");
        $this->query("SET NAMES 'utf8' COLLATE 'utf8_general_ci';");
        // $this->query("SET SESSION time_zone = 'Europe/Moscow';");
        //$this->query("alter session set time_zone = 'Europe/Moscow';");
        $this->query("SET SESSION time_zone = '+3:00';"); //*/
        $sql = "SET NAMES 'utf8' COLLATE 'utf8_general_ci';";
        $this->query($sql);
 //       $sql = "SET SESSION time_zone = '+3:00';";
 //       $this->query($sql);

    }

    public static function getInstance($forceCreate = false){
        if(is_null(self::$instance) || $forceCreate){
            self::$instance = new SQL();
        }
        return self::$instance;
    }

    public function query($SQL){

        $this->counter ++;

        $SQLresult = parent::query($SQL);

   //         Utils::forDebug($SQL);
        if($SQLresult === false){
            return new ErrorPoint(ErrorPoint::CODE_SQL, $this->error . "(SQL:" . $SQL . ")", ErrorPoint::TYPE_SYSTEM);
        }else{
            return $SQLresult;
        }
    }

    public function getInsertedId(){
        return $this->insert_id;
    }

    public function rollback(){ 

        parent::rollback();
        $this->autocommit(true);
    }

    public function commit(){

        parent::commit();
        $this->autocommit(true);
    }

    public function countOfQuery(){ 
        return $this->counter ;
    }
 
}

?>