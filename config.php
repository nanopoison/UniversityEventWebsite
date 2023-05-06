<?php
$host = 'localhost';
$user = 'root';
$pass = 'LionsBomb2021!';
$dbname = 'universityeventdatabase';

$link = mysqli_connect($host, $user, $pass, $dbname);

if (!$link) {
    die('Error: Unable to connect to database');
}

session_start();
?>
