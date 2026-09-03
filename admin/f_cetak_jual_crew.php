<link rel="stylesheet" href="../assets/css/paper.css">
<link rel="stylesheet" type="text/css" href="../assets/css/w3.css">
<link rel="stylesheet" href="../assets/css/blue-themes.css">
<?php
if(!session_id()) session_start();
include 'config.php';
include_once 'crew_helper.php';

$connect = opendtcek();
if(!$connect){
  exit("Koneksi database gagal");
}
ensureCrewTable($connect);
ensureMasJualCrewColumn($connect);

$kd_toko = isset($_SESSION['id_toko']) ? mysqli_real_escape_string($connect, $_SESSION['id_toko']) : '';
$tgl1 = isset($_POST['tgl1']) ? mysqli_real_escape_string($connect, $_POST['tgl1']) : '';
$tgl2 = isset($_POST['tgl2']) ? mysqli_real_escape_string($connect, $_POST['tgl2']) : '';
$kd_crew = isset($_POST['kd_crew']) ? mysqli_real_escape_string($connect, $_POST['kd_crew']) : '';

if($kd_toko === '' || $tgl1 === '' || $tgl2 === ''){
  exit("Parameter laporan belum lengkap");
}

$nm_toko = '';
$al_toko = '';
$cektoko = mysqli_query($connect, "SELECT nm_toko,al_toko FROM toko WHERE kd_toko='$kd_toko' LIMIT 1");
if($cektoko && mysqli_num_rows($cektoko) > 0){
  $dt_toko = mysqli_fetch_assoc($cektoko);
  $nm_toko = isset($dt_toko['nm_toko']) ? $dt_toko['nm_toko'] : '';
  $al_toko = isset($dt_toko['al_toko']) ? $dt_toko['al_toko'] : '';
}
if($cektoko){ mysqli_free_result($cektoko); }

$where = " WHERE mas_jual.kd_toko='$kd_toko' AND mas_jual.tgl_jual>='$tgl1' AND mas_jual.tgl_jual<='$tgl2' AND IFNULL(mas_jual.kd_crew,'')<>'' ";
if($kd_crew !== ''){
  $where .= " AND mas_jual.kd_crew='$kd_crew' ";
}

$q = mysqli_query($connect, "SELECT mas_jual.tgl_jual,mas_jual.no_fakjual,mas_jual.kd_crew,mas_jual.tot_jual,mas_jual.tot_disc,mas_jual.kd_bayar,crew.nm_crew
FROM mas_jual
LEFT JOIN crew ON mas_jual.kd_crew=crew.kd_crew AND crew.kd_toko=mas_jual.kd_toko
$where
ORDER BY mas_jual.tgl_jual ASC, mas_jual.no_urut ASC");
if(!$q){
  exit("Query laporan gagal: ".mysqli_error($connect));
}

$nm_crew_filter = 'SEMUA CREW';
if($kd_crew !== ''){
  $qcrew = mysqli_query($connect, "SELECT nm_crew FROM crew WHERE kd_crew='$kd_crew' AND kd_toko='$kd_toko' LIMIT 1");
  if($qcrew && mysqli_num_rows($qcrew) > 0){
    $dc = mysqli_fetch_assoc($qcrew);
    $nm_crew_filter = isset($dc['nm_crew']) ? $dc['nm_crew'] : $kd_crew;
  }else{
    $nm_crew_filter = $kd_crew;
  }
  if($qcrew){ mysqli_free_result($qcrew); }
}

function tgl_indo_singkat_crew($tgl){
  if($tgl == '' || $tgl == '0000-00-00'){ return '-'; }
  $x = explode('-', $tgl);
  if(count($x) !== 3){ return $tgl; }
  return $x[2].'-'.$x[1].'-'.$x[0];
}
?>
<style>
  body,h2,h3,h4,h5,h6 {font-family: Times,Helvetica}
  th{
    text-align: center;
    border: solid 1px #113300;
  }
  td{
    border: solid 1px #113300;
    background: white;
    font-size: 8pt;
    border-left: none;
    border-right: none;
    border-top: none;
  }
  .sheet {
    overflow: visible;
    height: auto !important;
  }
  @page { size: F4 landscape }
  @media print {
    #printPageButton { display: none; }
  }
</style>

<body class="F4 landscape">
  <section class="sheet padding-10mm">
    <table cellspacing="0" style="width:100%;font-size:8pt;">
      <thead>
        <tr><td colspan="8" style="text-align:center;font-size:13pt;border:none"><b><?=htmlspecialchars($nm_toko)?></b></td></tr>
        <tr><td colspan="8" style="text-align:center;font-size:11pt;border:none"><b><?=htmlspecialchars($al_toko)?></b></td></tr>
        <tr><td style="border:none">&nbsp;</td></tr>
        <tr><td colspan="8" style="text-align:left;font-size:9pt"><b>Laporan transaksi crew tanggal <?=tgl_indo_singkat_crew($tgl1)?> s/d <?=tgl_indo_singkat_crew($tgl2)?></b></td></tr>
        <tr><td colspan="8" style="text-align:left;font-size:9pt"><b>Filter crew: <?=htmlspecialchars($nm_crew_filter)?></b></td></tr>
        <tr class="yz-theme-l3">
          <th style="width:4%">NO</th>
          <th style="width:10%">TGL. JUAL</th>
          <th style="width:14%">NO. NOTA</th>
          <th style="width:12%">KODE CREW</th>
          <th>CREW</th>
          <th style="width:10%">PEMBAYARAN</th>
          <th style="width:12%">TOTAL JUAL</th>
          <th style="width:12%">NETTO</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $no = 0;
        $tot_jual = 0;
        $tot_netto = 0;
        while($d = mysqli_fetch_assoc($q)){
          $no++;
          $sub_total = floatval($d['tot_jual']);
          $netto = $sub_total - floatval($d['tot_disc']);
          $tot_jual += $sub_total;
          $tot_netto += $netto;
          ?>
          <tr>
            <td style="text-align:right;border-left:1px solid"><?=$no?>.</td>
            <td style="text-align:center"><?=tgl_indo_singkat_crew($d['tgl_jual'])?></td>
            <td style="text-align:left">&nbsp;<?=htmlspecialchars($d['no_fakjual'])?></td>
            <td style="text-align:center"><?=htmlspecialchars($d['kd_crew'])?></td>
            <td style="text-align:left">&nbsp;<?=htmlspecialchars(isset($d['nm_crew']) ? $d['nm_crew'] : '')?></td>
            <td style="text-align:center"><?=htmlspecialchars($d['kd_bayar'])?></td>
            <td style="text-align:right"><?=number_format($sub_total, 0, ',', '.')?></td>
            <td style="text-align:right;border-right:1px solid"><?=number_format($netto, 0, ',', '.')?></td>
          </tr>
          <?php
        }
        if($no === 0){
          ?>
          <tr>
            <td colspan="8" style="text-align:center;padding:12px;border-left:1px solid;border-right:1px solid">Tidak ada transaksi crew pada periode ini.</td>
          </tr>
          <?php
        } else {
          ?>
          <tr class="yz-theme-l3">
            <th colspan="6" style="text-align:right">TOTAL&nbsp;&nbsp;</th>
            <th style="text-align:right"><?=number_format($tot_jual, 0, ',', '.')?></th>
            <th style="text-align:right"><?=number_format($tot_netto, 0, ',', '.')?></th>
          </tr>
          <?php
        }
        mysqli_free_result($q);
        ?>
      </tbody>
    </table>

    <div class="w3-row w3-margin-top">
      <div class="w3-col w3-center">
        <button id="printPageButton" class="w3-btn w3-green" onclick="window.print();">Cetak PDF</button>
      </div>
    </div>
  </section>
</body>
<?php mysqli_close($connect); ?>
