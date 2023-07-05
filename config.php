<?php
$host = 'localhost';
$user = 'root';
//$pass = ''; // HID THIS PASSWORD SO I DONT HAVE IT PUBLIC
//$dbname = 'universityeventdatabase';

$link = mysqli_connect($host, $user, $pass, $dbname);

if (!$link) {
    die('Error: Unable to connect to database');
}

session_start();
?>
