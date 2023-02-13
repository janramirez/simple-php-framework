<?php

if($_SERVER['SERVER_NAME'] == 'localhost') {


    // DATABASE CONFIGURATION
    define('DBNAME','my_db');
    define('DBHOST','localhost');
    define('DBUSER','root');
    define('DBPASS','');
    define('DBDRIVER','');

    define('ROOT', 'http://localhost/simple-php-framework/public');

} else {

    // DATABASE CONFIGURATION
    define('DBNAME','my_db');
    define('DBHOST','localhost');
    define('DBUSER','root');
    define('DBPASS','');
    define('DBDRIVER','');


    define('ROOT', 'https://www.website.com');
}

define('APP_NAME',"My Website");
define('APP_DESC', "A simple PHP framework that utilizes the MVC Structure");

/** true=show errors, false=hide errors**/
define('DEBUG', true);