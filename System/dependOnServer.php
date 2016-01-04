<?php

switch ($runningOn){
    
    case 2:// Home
        define("MYSQL_HOST", "localhost");
        define("MYSQL_USER", "root");
        define("MYSQL_PASSWORD", "");
        define("MYSQL_DATA_BASE", "535698_football");

        define("SYSTEM_LOGS", "C:/srv/footboll/server/_logs");
        define("FILE_STORE_NEWS", "C:/srv/footboll/static/NEWS/");

        define("VK_API_SECRET", '****');
        define("VK_API_ID", '2014049');

        break;

    case 5: // Server
        define("MYSQL_HOST", "localhost");
        define("MYSQL_USER", "saveliev");
        define("MYSQL_PASSWORD", "****");
        define("MYSQL_DATA_BASE", "footballer");

        define("SYSTEM_LOGS", "/var/server/_logs");
        define("FILE_STORE_NEWS", "/var/www/nginx-default/content/NEWS/");

        define("VK_API_SECRET", '****');
        define("VK_API_ID", '2014049');

        break;
}




?>
