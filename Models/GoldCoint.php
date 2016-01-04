<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 18:11:01
 */

class GoldCoint {

    private $bonusForFirstPlace;
    private $bonusForSecondPlace;
    private $bonusForThirdPlace;

    function __construct($bonusForThirdPlace, $bonusForSecondPlace, $bonusForFirstPlace) {
        $this->bonusForFirstPlace = $bonusForFirstPlace;
        $this->bonusForSecondPlace = $bonusForSecondPlace;
        $this->bonusForThirdPlace = $bonusForThirdPlace;
    }

    public function getBonusForFirstPlace(){
        return $this->bonusForFirstPlace;
    }

    public function getBonusForSecondPlace(){
        return $this->bonusForSecondPlace;
    }

    public function getBonusForThirdPlace(){
        return $this->bonusForThirdPlace;
    }
 
}

?>
