<?php
// +----------------------------------------------------------------------+
// | IsMyFamily.name - History of your family                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2009 - 2010                                            |
// +----------------------------------------------------------------------+
// | Authors: Ivan Chura <ivan.chura@gmail.com>                           |
// +----------------------------------------------------------------------+

/**
 * @version 1.0
 * @author Ivan Chura <ivan.chura@gmail.com>  
 */
abstract class AbstractPrototype {

    public function getRequiredLevel(){
        return $this->required_level;
    }

    public function getId(){
        return $this->id;
    }

    public function getPrice(){
        return $this->price;
    }

    public function getRealPrice(){
        return $this->realPrice;
    }

}
?>