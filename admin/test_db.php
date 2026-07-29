<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "127.0.0.1";
$user = "u219974054_defafa";
$pass = "k8F!+0EYQgSG";
$db   = "u219974054_tokofafa";

$conn = @mysqli_connect($host, $user, $pass, $db, 3306);

if (!$conn) {
    die(
        "Error No : " . mysqli_connect_errno() .
        "<br>Error : " . mysqli_connect_error()
    );
}

echo "Koneksi Berhasil";