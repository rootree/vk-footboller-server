<?php
// +----------------------------------------------------------------------+
// | IsMyFamily.name - History of your family                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2009 - 2010                                            |
// +----------------------------------------------------------------------+
// | Authors: Ivan Chura <ivan.chura@gmail.com>                           |
// +----------------------------------------------------------------------+

/**
 * @version 1.0
 * @author Ivan Chura <ivan.chura@gmail.com>
 */
class Utils {

    public static function IdsSeparetedByComma($parameters){
        $forSQL = NULL;
        $count = 0;
        if(count($parameters) && (is_array($parameters) || is_object($parameters))){
            foreach ($parameters as $IdVar => $Id) {
                if(!empty($IdVar) && $count != $IdVar){
                    $forSQL .= $IdVar . ", ";
                }elseif(!is_object($Id) && !empty($Id)){
                    $forSQL .= $Id . ", ";
                }
                $count ++;
            }
            $forSQL = substr($forSQL, 0, -2);
        }
        return $forSQL;
    }

    public static function isEmpty($parameters){
        if(count($parameters) && (is_array($parameters) || is_object($parameters))){
            foreach ($parameters as $IdVar => $Id) {
                if(empty($IdVar)){
                    return false;
                }else{
                    return false;
                }
            }
        }
        return true;
    }

    public static function detectChanceOfWin(Team $userTeam, Team $enemyTeam){

        $userSumParameters = $userTeam->getParameterSum();
        $enemySumParameters = $userTeam->getParameterSum();

        if($userSumParameters == 0 || $enemySumParameters == 0){
            return 1;
        }

     /*   if($userSumParameters >= $enemySumParameters){
            return 1;
        }*/

        $max = max($userSumParameters, $enemySumParameters);
        $min = min($userSumParameters, $enemySumParameters);

        $percent = $min * 100 / $max;

        if($percent < 90){
            if($userSumParameters > $enemySumParameters){
                return 1;
            }else{
                return -1;
            }
        }

        $rand = mt_rand(0, 99);

        $SumParameters = $enemySumParameters + $userSumParameters;

        if($SumParameters == 0){
            $SumParameters = 100;
        }

        $userPercentParameters = floor($userSumParameters * 100 / $SumParameters);

        if($userTeam->getLevel() < 3){
            $userPercentParameters += 35;
        }elseif($userTeam->getLevel() < 6){
            $userPercentParameters += 30;
        }elseif($userTeam->getLevel() < 9){
            $userPercentParameters += 25;
        }

        if(($userPercentParameters - GlobalParameters::DETECT_RESULT_FOR_TIE < $rand)
                && ($userPercentParameters + GlobalParameters::DETECT_RESULT_FOR_TIE > $rand) ){ // стоит подумать о ничье
            $score = rand(1, 2) - 1;
        }elseif($rand <= $userPercentParameters){ // С учётом коефициента на ничью
            $score = 1;
        }else{
            $score = -1;
        }

        return $score;

    }

    public static function detectClearChanceOfWin($userSumParameters, $enemySumParameters, $allowTie = true){

		$max = max($userSumParameters, $enemySumParameters);
		$min = min($userSumParameters, $enemySumParameters);
		
		$percent = $min * 100 / $max;
	 
		if($percent < 90){
			if($userSumParameters > $enemySumParameters){
				return 1;
			}else{
				return -1;
			}
		}
		
        $rand = mt_rand(0, 99);

        $SumParameters = $enemySumParameters + $userSumParameters;

        if($SumParameters == 0){
            $SumParameters = 100;
        }

        
        $userPercentParameters = floor($userSumParameters * 100 / $SumParameters);

        if($allowTie && ($userPercentParameters - GlobalParameters::DETECT_RESULT_FOR_TIE < $rand)
                && ($userPercentParameters + GlobalParameters::DETECT_RESULT_FOR_TIE > $rand) ){ // стоит подумать о ничье
            $score = rand(1, 2) - 1;
        }elseif($rand <= $userPercentParameters){ // С учётом коефициента на ничью
            $score = 1;
        }else{
            $score = -1;
        }

        return $score;

    }


    public static function forDebug($message, $needVarDump = false){

        $output = ob_get_contents();
        @ob_end_clean();

        ob_start();

        echo "<ID:" . UserParameters::getUserId() . ">\n";

        echo(get_caller_method());
        echo '<print>' . PHP_EOL;
        print_r($message);
        echo '</print>' . PHP_EOL;

        if($needVarDump){
            echo '<var_dump>' . PHP_EOL;
            var_dump($message);
            echo '</var_dump>' . PHP_EOL;
        }

        echo "</ID:" . UserParameters::getUserId() . ">\n";

        $outputDebug = ob_get_contents();
        ob_end_clean();
        ob_start();

        fwrite(getLog(LOG_USER_DEBUG), $outputDebug . PHP_EOL . PHP_EOL);
        echo $output;

    }

    public static function logPayment($values){

        if($values <= 0){
            new ErrorPoint(ErrorPoint::CODE_LOGIC, "Как странно, как будто ничего не пополняем (" . $values . ")", ErrorPoint::TYPE_SYSTEM);
        }

        $sql_template =
"INSERT INTO payments (
    payments.vk_id,
    payments.paymant_date,
    payments.values
) VALUES (
    %d,
    NOW(),
    %d
)";

        $sql = sprintf($sql_template,
            UserParameters::getUserId(),
            $values
        );

        SQL::getInstance()->query($sql);

    }


    public static function assignObjectToObject(& $destinationObject, $scoreObject){ 
        foreach ($scoreObject as $paramName => $value) {
            if(isset($destinationObject->$paramName)){
                $destinationObject->$paramName = $value;
            }
        } 
    }
}
