<?php 
include 'config.php';
include_once 'crew_helper.php';
session_start();
$connect=opendtcek();
ensureCrewTable($connect);

$kd_toko = isset($_SESSION['id_toko']) ? $_SESSION['id_toko'] : '';
$nm_toko_sesi = isset($_SESSION['nm_toko']) ? $_SESSION['nm_toko'] : '';

$kd_crew = isset($_POST['kd_crew']) ? strtoupper(trim($_POST['kd_crew'])) : '';
$nm_crew = isset($_POST['nm_crew']) ? strtoupper(trim($_POST['nm_crew'])) : '';
$nm_toko = isset($_POST['nm_toko']) ? strtoupper(trim($_POST['nm_toko'])) : $nm_toko_sesi;
$al_crew = isset($_POST['al_crew']) ? strtoupper(trim($_POST['al_crew'])) : '';
$no_telp = isset($_POST['no_telp']) ? strtoupper(trim($_POST['no_telp'])) : '';
$aktif = isset($_POST['aktif']) ? 1 : 0;

if ($kd_toko === '' || $kd_crew === '' || $nm_crew === '') {
  header("location:m_crew.php?pesan=gagal");
  exit;
}

$kd_toko_esc = mysqli_real_escape_string($connect, $kd_toko);
$kd_crew_esc = mysqli_real_escape_string($connect, $kd_crew);
$nm_crew_esc = mysqli_real_escape_string($connect, $nm_crew);
$nm_toko_esc = mysqli_real_escape_string($connect, $nm_toko);
$al_crew_esc = mysqli_real_escape_string($connect, $al_crew);
$no_telp_esc = mysqli_real_escape_string($connect, $no_telp);

$cekkat=mysqli_query($connect,"SELECT no_urut FROM crew WHERE kd_crew='$kd_crew_esc' AND kd_toko='$kd_toko_esc' LIMIT 1");

if($cekkat && mysqli_num_rows($cekkat)>=1){
  $d=mysqli_query($connect,"UPDATE crew SET nm_crew='$nm_crew_esc',nm_toko='$nm_toko_esc',al_crew='$al_crew_esc',no_telp='$no_telp_esc',aktif=$aktif WHERE kd_crew='$kd_crew_esc' AND kd_toko='$kd_toko_esc'");
} else {
  $d=mysqli_query($connect,"INSERT INTO crew (kd_crew,nm_crew,nm_toko,al_crew,no_telp,aktif,kd_toko) VALUES('$kd_crew_esc','$nm_crew_esc','$nm_toko_esc','$al_crew_esc','$no_telp_esc',$aktif,'$kd_toko_esc')");
}
if($cekkat){ mysqli_free_result($cekkat); }

if($d){header("location:m_crew.php?pesan=simpan");}
else{header("location:m_crew.php?pesan=gagal");}

mysqli_close($connect);
?>
