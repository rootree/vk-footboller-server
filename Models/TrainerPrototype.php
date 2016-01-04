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
class TrainerPrototype extends AbstractPrototype{

    public $id;
    protected $required_level;
    protected $price;
    protected $realPrice;
    public $paramStudyRate;

    public function __construct($init){
        $this->id = $init->id;
        $this->required_level = $init->required_level;;
        $this->price = $init->price;
        $this->realPrice = $init->real_price;
        $this->paramStudyRate = $init->param_study_rate;
    }

}