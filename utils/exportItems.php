<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 23.06.2010
 * Time: 15:27:59
 */

define("SYSTEM_PATH", "..");
include_once(SYSTEM_PATH . "/System/settings.php");
include_once(SYSTEM_PATH . "/System/function.php");

define("TYPE_LOGO_SPONSOR", "Sponsor");

if(file_exists(ITEMS_XML)){
    $items = simplexml_load_file(ITEMS_XML);
}else{
    exit('Cannot open file ');
}

if(SQL::getInstance()->connect_error){ 
    exit('Cannot connect to DB');
}

SQL::getInstance()->query("TRUNCATE item_footballers");
SQL::getInstance()->query("TRUNCATE item_teamleads");
SQL::getInstance()->query("TRUNCATE item_sponsors");

foreach ($items->item as $item){
 
    $itemId = $item->attributes();
    $itemId = $itemId["id"];
    echo "Обработка #ID:" . $itemId . PHP_EOL;

    $SQL = NULL;

    switch($item->shopType){
        case TYPE_FOOTBALLER_FORWARD:
        case TYPE_FOOTBALLER_GOALKEEPER:
        case TYPE_FOOTBALLER_HALFSAFER:
        case TYPE_FOOTBALLER_SAFER:

            switch($item->shopType){
                case TYPE_FOOTBALLER_FORWARD: $line = TYPE_FOOTBALLER_FORWARD_CODE; break;
                case TYPE_FOOTBALLER_GOALKEEPER: $line = TYPE_FOOTBALLER_GOALKEEPER_CODE; break;
                case TYPE_FOOTBALLER_HALFSAFER: $line = TYPE_FOOTBALLER_HALFSAFER_CODE; break;
                case TYPE_FOOTBALLER_SAFER: $line = TYPE_FOOTBALLER_SAFER_CODE; break;
            }

            $SQL =
"INSERT INTO item_footballers (
    id,
    required_level,
    price,
    real_price,
    param_level,
    line
    ) VALUES (
    " . intval($itemId) . ",
    " . floatval($item->requiredLevel) . ",
    " . floatval($item->price) . ",
    " . floatval($item->realprice) . ",
    " . intval($item->params->level) . ",
    " . $line . "
    )";
            break;

        case TYPE_FOOTBALLER_TEAMLEAD:
            $SQL =
"INSERT INTO item_teamleads (
    id,
    required_level,
    price,
    real_price,
    param_study_rate
    ) VALUES (
    " . intval($itemId) . ",
    " . floatval($item->requiredLevel) . ",
    " . floatval($item->price) . ",
    " . floatval($item->realprice) . ",
    " . floatval($item->params->studyRate) . "
    )"; 
            break;

        case TYPE_LOGO_SPONSOR:
            $SQL =
"INSERT INTO item_sponsors (
    id,
    required_level,
    energy
    ) VALUES (
    " . intval($itemId) . ",
    " . floatval($item->requiredLevel) . ",
    " . floatval($item->params->energy) . "
    )";

            break;
    }

    if(!is_null($SQL)){
        SQL::getInstance()->query($SQL);    
    }

}
 
?>