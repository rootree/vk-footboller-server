<?php

/**
 * Description of Sponsor
 *
 * @author Administrator
 */
class Stadium {

    private $requiredLevel;

    private $dailyBonus;

    private $price;

    private $realPrice;

    private $id;

    public function initById($stadiumId) {

        $stadiumPrototype = RAM::getInstance()->getStadiumPrototypeById($stadiumId);

        if(empty($stadiumPrototype)){
 
            $sql_template = "SELECT * FROM item_stadiums WHERE id = '%s'";

            $sql = sprintf($sql_template,
                intval($stadiumId)
            );
            $SQLresult = SQL::getInstance()->query($sql);

            if($SQLresult instanceof ErrorPoint){
                return $SQLresult;
            }

            if($SQLresult->num_rows){
                $object = $SQLresult->fetch_object();
                $this->initFromParameters($object);
                RAM::getInstance()->setStadiumPrototype($object);
            }else{
                return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Не найден стадион по указаному номеру ", ErrorPoint::TYPE_SYSTEM);
            }

        }else{
            $this->initFromParameters($stadiumPrototype);
        }
    }

    public function initFromParameters($stadiumParameters) {
        $this->dailyBonus = $stadiumParameters->day_bonus;
        $this->id = $stadiumParameters->id;
        $this->requiredLevel = $stadiumParameters->required_level;
        $this->realPrice = $stadiumParameters->real_price;
        $this->price = $stadiumParameters->price;
    }

    public function getDailyBonus(){
        return $this->dailyBonus;
    }

    public function getRealPrice(){
        return $this->realPrice;
    }

    public function getPrice(){
        return $this->price;
    }

    public function getId(){
        return $this->id;
    }

    public function getRequiredLevel(){
        return $this->requiredLevel;
    }
}
?>
