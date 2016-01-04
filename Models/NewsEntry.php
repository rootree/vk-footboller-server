<?php

/**
 * Description of Sponsor
 *
 * @author Administrator
 */
class NewsEntry {

    public $sub_title;

    public $image;

    public $content;

    public $title;

    public $id;
 
    public function __construct($newsObject) {
        $this->id = $newsObject->news_id;
        $this->title = $newsObject->title;
        $this->content = $newsObject->content;
        $this->image = $newsObject->image;
        $this->sub_title = $newsObject->sub_title;
    }
    
}
?>
