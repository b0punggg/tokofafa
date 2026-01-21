<?php
  ob_start();
  include_once 'config.php';
  session_start();
  
  $cDtc  = $_POST['dtc'];
  $nKali = isset($_POST['kopi']) ? $_POST['kopi'] : 1;
  
  // Parse dtc untuk mendapatkan data yang diperlukan
  // Format dtc: no_fakjual;tgl_jual;kd_toko;nm_pel;alamat;tgltime;disctot;voucher;ongkir;kd_bayar;bayar;susuk;saldohut;tgl_jt
  $xd = explode(';', $cDtc);
  $no_fakjual = isset($xd[0]) ? $xd[0] : '';
  $tgl_jual   = isset($xd[1]) ? $xd[1] : '';
  $kd_toko    = isset($xd[2]) ? $xd[2] : '';
  $nm_pel     = isset($xd[3]) ? $xd[3] : '';
  $alamat     = isset($xd[4]) ? $xd[4] : '';
  $tgltime    = isset($xd[5]) ? $xd[5] : '';
  $disctot    = isset($xd[6]) ? floatval($xd[6]) : 0;
  $voucher    = isset($xd[7]) ? floatval($xd[7]) : 0;
  $ongkir     = isset($xd[8]) ? floatval($xd[8]) : 0;
  $kd_bayar   = isset($xd[9]) ? $xd[9] : 'TUNAI';
  $bayar      = isset($xd[10]) ? floatval($xd[10]) : 0;
  $susuk      = isset($xd[11]) ? floatval($xd[11]) : 0;
  $saldohut   = isset($xd[12]) ? floatval($xd[12]) : 0;
  $tgl_jt     = isset($xd[13]) ? $xd[13] : '';
  
  // Ambil kd_pel dari database berdasarkan mas_jual atau dum_jual
  $connect = opendtcek();
  $kd_pel = 'IDPEL-0'; // Default
  
  // Cari kd_pel dari mas_jual jika ada
  $cekpel = mysqli_query($connect, "SELECT kd_pel FROM mas_jual WHERE no_fakjual='$no_fakjual' AND tgl_jual='$tgl_jual' AND kd_toko='$kd_toko' LIMIT 1");
  if (mysqli_num_rows($cekpel) > 0) {
    $dtpel = mysqli_fetch_assoc($cekpel);
    $kd_pel = $dtpel['kd_pel'];
  } else {
    // Jika tidak ada di mas_jual, cek dari dum_jual
    $cekpel2 = mysqli_query($connect, "SELECT kd_pel FROM dum_jual WHERE no_fakjual='$no_fakjual' AND tgl_jual='$tgl_jual' AND kd_toko='$kd_toko' LIMIT 1");
    if (mysqli_num_rows($cekpel2) > 0) {
      $dtpel2 = mysqli_fetch_assoc($cekpel2);
      $kd_pel = $dtpel2['kd_pel'];
    }
    mysqli_free_result($cekpel2);
  }
  mysqli_free_result($cekpel);
  
  // Format keyword untuk f_jualcetaknota.php: kd_toko;kd_pel;no_fakjual;tgl_jual;d;kd_bayar;bayar;saldohut;tgl_jt;susuk
  $d = ''; // Parameter tambahan
  $keyword = $kd_toko . ';' . $kd_pel . ';' . $no_fakjual . ';' . $tgl_jual . ';' . $d . ';' . $kd_bayar . ';' . $bayar . ';' . $saldohut . ';' . $tgl_jt . ';' . $susuk;
  
  // Simpan keyword ke $_POST agar f_jualcetaknota.php bisa mengaksesnya
  $_POST['keyword'] = mysqli_real_escape_string($connect, $keyword);
  
  // Panggil f_jualcetaknota.php langsung untuk cetak ke thermal printer
  // File ini akan langsung mencetak ke printer tanpa perlu response
  include 'f_jualcetaknota.php';
  
  mysqli_close($connect);
  
  $html = ob_get_contents(); 
  ob_end_clean();
  echo json_encode(array('hasil'=>$html));
?>
