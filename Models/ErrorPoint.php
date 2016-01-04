<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 19:39:49
 */

class ErrorPoint {

    public static $isNeedToLog = false;

    const CODE_BAD_MD5 = 1001;

    const CODE_FORBIDDEN_REQUEST = 1004;

    const CODE_PARAMETERS = 1003;

    const CODE_BAD_COMMAND = 1002;

    const CODE_SYSTEM = 1005;

    const CODE_SQL = 1006;

    const CODE_RAM = 1010;

    const CODE_LOGIC = 1007;

    const CODE_SECURITY = 1008;

    const CODE_VK = 1009;

    
    const TYPE_USER = "userError";

    const TYPE_SYSTEM = "systemError";

    private $message;

    private $code;

    private $type;

    public function __construct($code, $message, $type, $trace = NULL){

        ErrorPoint::$isNeedToLog = true;

        $this->code = $code;
        $this->message = $message;
        $this->type = $type;

        if($this->type == ErrorPoint::TYPE_USER){
            trigger_error($this->message, E_USER_NOTICE);
        }else{
            error_log(getErrorString($this->message));
        }
    }

    public function getMessage(){
        if($this->type == ErrorPoint::TYPE_USER){
            return $this->message;
        }else{
            return "Ошибка в работе программы.";
        }
    }

}
