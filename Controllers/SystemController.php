<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 17:53:21
 */

class SystemController extends Controller implements IController{

    const COMMAND_GET_ON_LINE = 'getOnLine';
    const COMMAND_GET_PAYMENTS = 'getTodayPayments';

    public function getResult(){ 
        return $this->result;
    }
    
    public function action(){

        if(!isset($this->parameters->subCommand)){
            return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Cистемный параметр не найден", ErrorPoint::TYPE_SYSTEM);
        }

        switch($this->parameters->subCommand){
            case SystemController::COMMAND_GET_ON_LINE:
                $this->getOnLine();
                break;
            case SystemController::COMMAND_GET_PAYMENTS:
                $this->getTodayPayments();
                break;
            default:
                return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Cистемная команда не найдена", ErrorPoint::TYPE_SYSTEM); 
        } 
    }

    private function getOnLine(){

        track_stats(); // Отслеживаем производительность

        $sql_template =
"SELECT vk_id
FROM user_actions
WHERE date_sing_in > (NOW() -  INTERVAL %d MINUTE)";

        $sql = sprintf($sql_template, GlobalParameters::ON_LINE_RANGE);

        $onLineResult = SQL::getInstance()->query($sql);

        if($onLineResult instanceof ErrorPoint){
            return $onLineResult;
        }

        track_stats(); // Отслеживаем производительность
        
        $this->result['onLine'] = abs($onLineResult->num_rows - 1);
 
    }

    private function getTodayPayments(){

        track_stats(); // Отслеживаем производительность

        $sql_template =
"SELECT SUM(payments.values) as summer FROM payments WHERE paymant_date > '%s 00:00:00';";

        $sql = sprintf($sql_template, date("Y-m-d"));

        $onLineResult = SQL::getInstance()->query($sql);

        if($onLineResult instanceof ErrorPoint){
            return $onLineResult;
        }

        track_stats(); // Отслеживаем производительность

        $object = $onLineResult->fetch_object();

        $this->result['todayPayments'] = intval($object->summer) ;

    }
}
