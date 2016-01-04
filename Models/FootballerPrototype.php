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
class FootballerPrototype extends AbstractPrototype{

    public $id;
    protected $required_level;
    protected $price;
    protected $realPrice;
    private $paramLevel;
    private $line;
    private $isFriend; 
    private $name;
    private $photo; 
    private $year;
    private $country;
    private $healthDown;

    public function getYear(){
        return $this->year;
    }

    public function getCountry(){
        return $this->country;
    }

    public function __construct(){
    }
 
    public function init($init, $parameters){

        if(isset($parameters->isFriend)){
            $this->id = $parameters->peopleId;
            $this->required_level = 1; 
            $this->price = $init->price;
            $this->realPrice = 0;
            $this->paramLevel = $init->level;
            $this->healthDown = $init->healthDown;
            $this->line = 0;
            $this->isFriend = 1;
            $this->name = $parameters->name;
            $this->photo = $parameters->photo;
        }else{
            $this->id = $init->id;
            $this->required_level = $init->required_level;;
            $this->price = $init->price;
            $this->realPrice = $init->real_price;
            $this->paramLevel = $init->param_level;
            $this->healthDown = (isset($init->health_down)) ? $init->health_down : 0;
            $this->line = $init->line;
            $this->isFriend = 0;
            $this->name = "";
            $this->photo = "";
        }
    }

    public function getLine(){
        return $this->line;
    } 

    public function getParamLevel(){
        return $this->paramLevel;
    }

    public function getIsFriend(){
        return $this->isFriend;
    }

    public function getName(){
        return $this->name;
    }

    public function getPhoto(){
        return $this->photo;
    }

    public function getHealthDown(){
        return $this->healthDown;
    }

}
