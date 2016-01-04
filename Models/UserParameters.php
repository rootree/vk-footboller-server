<?php
// +----------------------------------------------------------------------+
// | Copyright (c) 2009 - 2010                                            |
// +----------------------------------------------------------------------+
// | Authors: Ivan Chura <ivan.chura@gmail.com>                           |
// +----------------------------------------------------------------------+

/**
 * @version 1.0
 * @author Ivan Chura <ivan.chura@gmail.com>  
 */
class UserParameters {
 
    private static $userId;

    private static $authKey;

    public static function getUserId(){
        if(isset(self::$userId))
            return self::$userId;
        else
            return 0;
    }

    public static function getAuthKey(){
        return self::$authKey;
    }

    public static function setUserId($userId){
        self::$userId = $userId;
    }

    public static function setAuthKey($authKey){
        self::$authKey = $authKey;
    }
}
