<?php
/**
 * Created by IntelliJ IDEA.
 * User: Администратор
 * Date: 25.04.2010
 * Time: 14:13:02
 * To change this template use File | Settings | File Templates.
 */

class Score{

    public $result;
    public $goals;
    public $enemyGoals;

    function __construct($result, $goals, $enemyGoals) {
        $this->result = $result;
        $this->goals = $goals;
        $this->enemyGoals = $enemyGoals;
    }

}
