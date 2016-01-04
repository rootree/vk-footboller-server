<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 17:48:45
 */

class BuyStudyPointsController extends Controller implements IController{

    private $payments;

    function __construct($parameters) {

        parent::__construct($parameters);

        $this->payments = array(
            101 => new PaymentEntity(101, 5, $this->teamProfile->getStudyPointCostForCount(5), $this->teamProfile->getStudyPointRealCostForCount(5)),
            102 => new PaymentEntity(102, 15, $this->teamProfile->getStudyPointCostForCount(15), $this->teamProfile->getStudyPointRealCostForCount(15)),
            103 => new PaymentEntity(103, 50, $this->teamProfile->getStudyPointCostForCount(50), $this->teamProfile->getStudyPointRealCostForCount(50)),
            104 => new PaymentEntity(104, 99, $this->teamProfile->getStudyPointCostForCount(99), $this->teamProfile->getStudyPointRealCostForCount(99))
        );
    } 

    public function getResult(){
        $this->result['balance'] = array(
            "money" => $this->teamProfile->getMoney(),
            "realMoney" => $this->teamProfile->getRealMoney(),
            "studyPoints" => $this->teamProfile->getStudyPoints()
        );
        return $this->result;
    }

    public function action(){

        $actionResult = NULL;

        $isInGame = $this->parameters->isInGame;
        $paymentId = $this->parameters->paymentId;

        if(!array_key_exists($paymentId, $this->payments)){
            return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Не выбран тип покупки", ErrorPoint::TYPE_USER);
        }

        $paymentInstance = $this->payments[$paymentId];

        if($isInGame){
            if($paymentInstance->price > $this->teamProfile->getMoney()){
                return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Недостаточно денежных средств", ErrorPoint::TYPE_USER);
            }
        }else{
            if($paymentInstance->realPrice > $this->teamProfile->getRealMoney()){
                return new ErrorPoint(ErrorPoint::CODE_LOGIC, "Недостаточно денежных средств", ErrorPoint::TYPE_USER);
            }
        }

        track_stats(); // Отслеживаем производительность

        SQL::getInstance()->autocommit(false);

        $this->teamProfile->setStudyPoints($this->teamProfile->getStudyPoints() + $paymentInstance->studyCount);

        if($this->parameters->isInGame){
            $this->teamProfile->setMoney($this->teamProfile->getMoney() - $paymentInstance->price);
        }else{
            $this->teamProfile->setRealMoney($this->teamProfile->getRealMoney() - $paymentInstance->realPrice);
        }

        $actionResult = $this->teamProfile->save();

        if($actionResult instanceof ErrorPoint){
            SQL::getInstance()->rollback();
        }else{
            SQL::getInstance()->commit();
        }

        track_stats(); // Отслеживаем производительность
        
        return $actionResult;

    }

}
