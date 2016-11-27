<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'emergency_resource_management_system');

define('RESOURCE_REQUEST_DURATION', '+5 day');
define('RESOURCE_REPAIR_DURATION', '+5 day');

date_default_timezone_set('UTC');

$mysqli = new mysqli(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASE);


if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
else{
    $mysqli->set_charset('utf8');
    $mysqli->query("SET TIME_ZONE = '+0:00'");
}

