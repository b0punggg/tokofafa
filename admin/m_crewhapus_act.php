<?php 
include "config.php";
session_start();
$connect=opendtcek();
$kd_toko = isset($_SESSION['id_toko']) ? mysqli_real_escape_string($connect, $_SESSION['id_toko']) : '';
$id = isset($_GET['param']) ? (int)$_GET['param'] : 0;
if ($id > 0 && $kd_toko !== '') {
  $f=mysqli_query($connect, "DELETE FROM crew WHERE no_urut=$id AND kd_toko='$kd_toko'");
  if($f){
    header("location:m_crew.php?pesan=hapus");
    mysqli_close($connect);
    exit;
  }
}
mysqli_close($connect);
header("location:m_crew.php?pesan=gagal");
?>
