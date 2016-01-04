<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 23.06.2010
 * Time: 17:49:38
 */

class GlobalParameters {

    const MAX_TOUR_BONUS = 1.7;

    const ENERGY_PER_MATCH = 16;

    const EXPERIANCE_PER_MATCH = 5;

    const EXPERIANCE_PER_MATCH_LOSE = 1;

    const EXPERIANCE_PER_MATCH_TIE = 2;

    const MONEY_PER_MATCH = 86;

    const MONEY_PER_MATCH_LOSE = 10;

    const MONEY_PER_MATCH_TIE = 45;
 
    const ENEMY_RANGE = 30;

    const START_LEVEL = 1;

    const LEVEL_MAX = 51;

    const START_ENERGY = 10;

    const START_MONEY = 5000;

    const START_REAL_MONEY = 15;

    const MAX_TEAM = 11;

    const MAX_FRIENDS_IN_TEAM = 11; 

    const SPONSORS_LIMIT = 3;

    const ON_LINE_RANGE = 5; // мин

    const DETECT_RESULT_FOR_TIE = 10; // пунктов

    const DETECT_RESULT_FOR_CHEAT = 55; // если пытаються наебать

    const GROUP_BONUS_REAL = 10; // если пытаються наебать

    const STUDY_POINT_BASE_COST = 400; // если пытаються наебать

    const SUPER_PRICE = 30; // супер футболист

    const PRICE_FRESH_MONEY = 5000; // ЦЕНА пополнения энергии

    const PRICE_FRESH_REAL_MONEY = 3; // ЦЕНА пополнения энергии

    const REAL_VS_INGAME = 400; // ЦЕНА пополнения энергии

    const MODER_ID = 100206819; // ЦЕНА пополнения энергии


    private static $command;
    private static $groupId;

    public static $IS_FAKE_ENTER = false;

    public static function setCommand($value){
        GlobalParameters::$command = $value;
    }

    public static function setGroupId($value){
        GlobalParameters::$groupId = $value;
    }

    public static function getCommand(){
        return GlobalParameters::$command;   
    }

    public static function getGroupId(){
        return GlobalParameters::$groupId;
    }

}
