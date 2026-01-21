<?php
include "config.php";
session_start();

$kd_member = isset($_POST['kd_member']) ? $_POST['kd_member'] : '';

$connect = opendtcek();
$poin = 0;

if($kd_member != ''){
  $cek = mysqli_query($connect, "SELECT poin FROM member WHERE kd_member='$kd_member'");
  if(mysqli_num_rows($cek) > 0){
    $data = mysqli_fetch_assoc($cek);
    $poin = isset($data['poin']) ? floatval($data['poin']) : 0;
  }
  mysqli_free_result($cek);
}

mysqli_close($connect);

echo json_encode(array('poin' => $poin));
?>
