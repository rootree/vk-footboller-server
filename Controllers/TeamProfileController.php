<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 17:54:45
 */

class TeamProfileController extends Controller implements IController{

    public function getResult(){
        return $this->result;
    }

    public function action(){
 
        $team = new Team();
        $team->initById($this->parameters->teamId);

        $teamInJSON = JSONPrepare::team($team);
        $teamInJSON["footballers"] = JSONPrepare::footballers(FootballerSatellite::initForTeam($team));;
        $teamInJSON["sponsors"] = JSONPrepare::sponsors(SponsorSatellite::initForTeam($team));;

        $this->result['team'] = $teamInJSON;
    } 
}
