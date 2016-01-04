<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 17:47:22
 */

class GetEnergyController extends Controller implements IController {

    public function getResult(){ 
        $this->result['currentEnergy'] = $this->teamProfile->getCurrentEnergy() + rand(0,20);
        return $this->result;
    }

    public function action(){
    }
}

?>