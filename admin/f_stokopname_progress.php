<?php
include 'config.php';
if (!session_id()) {
  session_start();
}
header('Content-Type: application/json; charset=UTF-8');

$connect = opendtcek();
$kd_toko = '';
if ($connect) {
  $kd_toko = isset($_SESSION['id_toko']) ? mysqli_real_escape_string($connect, $_SESSION['id_toko']) : '';
}

$tgl_awal = isset($_POST['tgl_awal']) ? trim($_POST['tgl_awal']) : '';
$tgl_akhir = isset($_POST['tgl_akhir']) ? trim($_POST['tgl_akhir']) : '';

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl_awal) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl_akhir)) {
  echo json_encode(array('ok' => false, 'msg' => 'Format tanggal tidak valid.'));
  if ($connect) {
    mysqli_close($connect);
  }
  exit;
}

if ($tgl_awal > $tgl_akhir) {
  echo json_encode(array('ok' => false, 'msg' => 'Tanggal awal tidak boleh lebih besar dari tanggal akhir.'));
  if ($connect) {
    mysqli_close($connect);
  }
  exit;
}

$total = 0;
$sudah = 0;

if (!$connect || $kd_toko === '') {
  echo json_encode(array(
    'ok' => false,
    'msg' => 'Koneksi atau data toko tidak tersedia.',
  ));
  exit;
}

$tgl_awal_esc = mysqli_real_escape_string($connect, $tgl_awal);
$tgl_akhir_esc = mysqli_real_escape_string($connect, $tgl_akhir);

$q_total = mysqli_query(
  $connect,
  "SELECT COUNT(*) AS jumlah FROM (
    SELECT kd_brg FROM beli_brg WHERE kd_toko='$kd_toko' GROUP BY kd_brg
  ) t"
);
if ($q_total) {
  $row_total = mysqli_fetch_assoc($q_total);
  $total = isset($row_total['jumlah']) ? (int) $row_total['jumlah'] : 0;
  mysqli_free_result($q_total);
}

$q_sudah = mysqli_query(
  $connect,
  "SELECT COUNT(DISTINCT kd_brg) AS jumlah FROM mutasi_adj
   WHERE kd_toko='$kd_toko'
   AND tgl_input >= '$tgl_awal_esc'
   AND tgl_input <= '$tgl_akhir_esc'
   AND INSTR(UPPER(ket), 'PENYESUAIAN') > 0
   AND INSTR(UPPER(ket), 'LINE') = 0"
);
if ($q_sudah) {
  $row_sudah = mysqli_fetch_assoc($q_sudah);
  $sudah = isset($row_sudah['jumlah']) ? (int) $row_sudah['jumlah'] : 0;
  mysqli_free_result($q_sudah);
}

$belum = max(0, $total - $sudah);
$persen = $total > 0 ? round(($sudah / $total) * 100, 1) : 0;

mysqli_close($connect);

echo json_encode(array(
  'ok' => true,
  'total' => $total,
  'sudah' => $sudah,
  'belum' => $belum,
  'persen' => $persen,
  'tgl_awal' => $tgl_awal,
  'tgl_akhir' => $tgl_akhir,
  'tgl_awal_txt' => gantitgl($tgl_awal),
  'tgl_akhir_txt' => gantitgl($tgl_akhir),
));
