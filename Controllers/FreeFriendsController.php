<?php
// +----------------------------------------------------------------------+
// | Обработка создания команды                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2009 - 2010                                            |
// +----------------------------------------------------------------------+
// | Authors: Ivan Chura <ivan.chura@gmail.com>                           |
// +----------------------------------------------------------------------+

/**
 * @version 1.0
 * @author Ivan Chura <ivan.chura@gmail.com>
 * @desc На самом деле получение списка уже занятых друзей
 */
class FreeFriendsController extends Controller implements IController {

    public function getResult(){
        return $this->result;
    }

    public function action(){
        $this->result["friendsForChoose"] = $this->getFreeFriends($this->parameters);
    }

    private function getFreeFriends(){

        $friendsStore = array();

 /*       if($this->parameters){

            $fiendsForSQL = Utils::IdsSeparetedByComma($this->parameters);
			
			if(empty($fiendsForSQL)){
				return $friendsStore;
			}
			
            $sql_template = "SELECT vk_id FROM teams WHERE in_team = 0 AND vk_id in (%s)"; 
            $sql = sprintf($sql_template, 
                $fiendsForSQL
            );
 
            $SQLresult = SQL::getInstance()->query($sql);

            if($SQLresult instanceof ErrorPoint){
                return $SQLresult;
            }

            if($SQLresult->num_rows){
                while ($friend = $SQLresult->fetch_object()){
                    $friendsStore[$friend->vk_id] = 'in';
                }
            }
        }
        */
        return $friendsStore;
    }

}