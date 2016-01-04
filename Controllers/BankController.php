<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 08.07.2010
 * Time: 11:46:26
 */

class BankController extends Controller implements IController {
 
    public function getResult(){
        return $this->result;
    }

    public function action(){
        
        $withdraw = intval($this->parameters->value);
        $moneyType = intval($this->parameters->moneyType);

        $api = new VKapi(VK_API_SECRET, VK_API_ID, VK_MAILING_SPEED);

        $balanceResult = $api->getBalance(UserParameters::getUserId());
 
        if(isset($balanceResult["error"])){
            if(isset($balanceResult["error"]["error_code"]) && $balanceResult["error"]["error_msg"]){
                return new ErrorPoint(ErrorPoint::CODE_VK, "Ошибка социальной сети (" . $balanceResult["error"]["error_code"]. ":" .
                        $balanceResult["error"]["error_msg"] . ")", ErrorPoint::TYPE_SYSTEM);
            }else{
                return new ErrorPoint(ErrorPoint::CODE_VK, "Ошибка социальной сети", ErrorPoint::TYPE_SYSTEM);
            }
        }

        $userBalance = $balanceResult["response"];
       
        if(($withdraw * 100) > $userBalance){
            return new ErrorPoint(ErrorPoint::CODE_SECURITY, "На вашем балансе недостаточно средств", ErrorPoint::TYPE_USER);
        }

        track_stats(); // Отслеживаем производительность

        $balanceResult = $api->withdrawVotes(UserParameters::getUserId(), $withdraw * 100);

        track_stats(); // Отслеживаем производительность

        if($balanceResult instanceof ErrorPoint){
            return $balanceResult;
        }

        if(isset($balanceResult["error"])){
            return new ErrorPoint(ErrorPoint::CODE_VK, "Ошибка социальной сети (" . $balanceResult["error"]["error_code"]. ":" .
                    $balanceResult["error"]["error_msg"] . ")", ErrorPoint::TYPE_SYSTEM);
        }

        if($moneyType == MONEY_TYPE_GAME){
            $this->teamProfile->setMoney($withdraw * EXCHANGE_RATE_GAME + $this->teamProfile->getMoney());
        }else{
            $this->teamProfile->setRealMoney($withdraw * EXCHANGE_RATE_REAL + $this->teamProfile->getRealMoney());
        }
        
        $actionResult = $this->teamProfile->save();

        track_stats(); // Отслеживаем производительность

        $this->result['balance']['money'] = $this->teamProfile->getMoney();
        $this->result['balance']['realMoney'] = $this->teamProfile->getRealMoney();

        Utils::logPayment($withdraw);

        track_stats(); // Отслеживаем производительность
        
        return $actionResult;

    }
}
