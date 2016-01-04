<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 18:11:01
 */

class PaymentEntity {

    public $paymentId;
    public $studyCount;
    public $price;
    public $realPrice;


    function __construct($paymentId, $studyCount, $price, $realPrice) {
        $this->paymentId = $paymentId;
        $this->studyCount = $studyCount;
        $this->price = $price;
        $this->realPrice = $realPrice;
    }
 
}

?>
