<?php
/**
 * User: Ivan Chura <ivan.chura@gmail.com>
 * Date: 22.06.2010
 * Time: 19:09:48
 */




$runningOn = 1;
switch ($runningOn){
    case 1: // Server
        define("SYSTEM_PATH", "/var/server");
        break;
    case 2: // Home
        define("SYSTEM_PATH", "..");
        break;
}
 
include_once(SYSTEM_PATH . "/System/settings.php");
include_once(SYSTEM_PATH . "/System/function.php");

echo "Running news updater ... " . date("[Y-m-d H:i:s.m]") . PHP_EOL ;

register_shutdown_function('shutdown');
set_error_handler("handlerError");

$actionResult = null;

if(SQL::getInstance()->connect_error){
    $actionResult = new ErrorPoint(ErrorPoint::CODE_SQL, "Соединение провалено (" . SQL::getInstance()->connect_error . ")", ErrorPoint::TYPE_SYSTEM);
    die("No connection");
}

$baseUrl = "http://www.euro-football.ru";

$serverResponse = getPage($baseUrl . "/category/archive/0/1");
 
preg_match_all('/<a href="(.+?)">(.+?)<\/a>.+?<img align="left" src="(.+?)" alt=.+?<p>(.+?)<\/p>/ms', $serverResponse, $matches, PREG_PATTERN_ORDER);

$newsStore = array();
$count = -1;

if(count($matches[3])){
    $sql_template = 'DELETE FROM news_sport ;';
    $sql = $sql_template;
    SQL::getInstance()->query($sql);
}

foreach($matches[3] as $key => $trash){

    if($count > NEWS_LIMIT){
        break;
    }

    $count ++;

    $link      = trim($matches[1][$key]);
    $title     = trim($matches[2][$key]);
    $imageLink = trim($matches[3][$key]);

    $newsStore[$count] = array(
        "title"       => $title,
        "imageLink"   => $imageLink,       
    );

    $newsLink = $baseUrl . $link;
    $serverResponse = getPage($newsLink);

    preg_match('/<h1 class="t">(.+?)<\/h1>.+?<\/div>\s+<p>(.+?)<!--more--><\/p>\s+<p> <\/p>(.+?)<p class="source">/ms', $serverResponse, $match);

    if(count($match)){

        $subTitle = $match[2];
        $mainText = $match[3];

        if(strlen($mainText) > 2000){
            $count --;
            continue;
        }

        $badText = "Представляем Вашему вниманию";
        if(strpos($mainText, $badText)){
            $count --;
            continue;
        }

        $badText = "<p>Прямая трансляция матча";

        if(strpos($mainText, $badText)){
            $mainText = substr($mainText, 0, strpos($mainText, $badText));
        }

        $mainText = strip_tags($mainText);
        $mainText = trim($mainText);
        $mainText = str_replace("ё", "е", $mainText);
        $mainText = str_replace("Ё", "Е", $mainText);

        $subTitle = strip_tags($subTitle);
        $subTitle = trim($subTitle);
        $subTitle = str_replace("ё", "е", $subTitle);
        $subTitle = str_replace("Ё", "Е", $subTitle);

        $destinationName = "news_image_" . $count;
        copyPhotoFile("http://www.euro-football.ru" . $imageLink, $destinationName . ".jpg");

       // $imageLink = substr($imageLink, 0, -4);

        $sql_template = 'INSERT INTO news_sport (title, content, image, sub_title) values("%s", "%s", "%s", "%s");';
        $sql = sprintf($sql_template,
            SQL::getInstance()->real_escape_string($title),
            SQL::getInstance()->real_escape_string($mainText),
            SQL::getInstance()->real_escape_string($destinationName),
            SQL::getInstance()->real_escape_string($subTitle)
        );

        $SQLresultTemp = SQL::getInstance()->query($sql);
        if($SQLresultTemp instanceof ErrorPoint){
            continue;
        }

        $newsObject = new stdClass();
        $newsObject->news_id = $count;
        $newsObject->title = $title;
        $newsObject->content = $mainText;
        $newsObject->image = $destinationName;
        $newsObject->sub_title = $subTitle;

        $news = new NewsEntry($newsObject); 
        RAM::getInstance()->setNews($news);
 
    }else{
        $count --; 
    }

}

echo str_repeat(" ", 27) . date("[Y-m-d H:i:s.m]") . " " . PHP_EOL;
 
?>