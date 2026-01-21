<?php
  ob_start();
  include_once 'config.php';
  session_start();
  
  // Jika ada action untuk get kd_pel
  if (isset($_POST['action']) && $_POST['action'] == 'get_kd_pel') {
    $no_fakjual = $_POST['no_fakjual'];
    $tgl_jual = $_POST['tgl_jual'];
    $kd_toko = $_POST['kd_toko'];
    
    $connect = opendtcek();
    $kd_pel = 'IDPEL-0';
    
    $cekpel = mysqli_query($connect, "SELECT kd_pel FROM mas_jual WHERE no_fakjual='$no_fakjual' AND tgl_jual='$tgl_jual' AND kd_toko='$kd_toko' LIMIT 1");
    if (mysqli_num_rows($cekpel) > 0) {
      $dtpel = mysqli_fetch_assoc($cekpel);
      $kd_pel = $dtpel['kd_pel'];
    } else {
      $cekpel2 = mysqli_query($connect, "SELECT kd_pel FROM dum_jual WHERE no_fakjual='$no_fakjual' AND tgl_jual='$tgl_jual' AND kd_toko='$kd_toko' LIMIT 1");
      if (mysqli_num_rows($cekpel2) > 0) {
        $dtpel2 = mysqli_fetch_assoc($cekpel2);
        $kd_pel = $dtpel2['kd_pel'];
      }
      mysqli_free_result($cekpel2);
    }
    mysqli_free_result($cekpel);
    mysqli_close($connect);
    
    header('Content-Type: application/json');
    echo json_encode(array('kd_pel' => $kd_pel));
    exit;
  }
  
  $cDtc  = $_POST['dtc'];
  $nKali = isset($_POST['kopi']) ? $_POST['kopi'] : 1;
?>
  <script>
  console.log('🔵 Cetak nota script loaded, dtc: <?=$cDtc?>');
  (function() {
    // Flag untuk mencegah double execution
    var printKey = 'print_' + '<?=$cDtc?>';
    
    // Cek apakah sudah ada flag di sessionStorage
    if (sessionStorage.getItem(printKey)) {
      console.log('⚠️ Print already executed, skipping...');
      return;
    }
    
    console.log('✅ Starting print process...');
    
    // Set flag
    sessionStorage.setItem(printKey, '1');
    
    // Hapus flag setelah 3 detik
    setTimeout(function() {
      sessionStorage.removeItem(printKey);
    }, 3000);

    // Parse dtc untuk mendapatkan data yang diperlukan
    var dtcParts = '<?=$cDtc?>'.split(';');
    // Format dtc: no_fakjual;tgl_jual;kd_toko;nm_pel;alamat;tgltime;disctot;voucher;ongkir;kd_bayar;bayar;susuk;saldohut;tgl_jt
    // f_jualcetaknota.php membutuhkan: kd_toko;kd_pel;no_fakjual;tgl_jual;d;kd_bayar;bayar;saldohut;tgl_jt;susuk
    
    var kd_toko = dtcParts[2] || '';
    var no_fakjual = dtcParts[0] || '';
    var tgl_jual = dtcParts[1] || '';
    var kd_bayar = dtcParts[9] || 'TUNAI';
    var bayar = dtcParts[10] || '0';
    var saldohut = dtcParts[12] || '0';
    var tgl_jt = dtcParts[13] || '';
    var susuk = dtcParts[11] || '0';
    
    // Ambil kd_pel dari server via AJAX
    console.log('🖨️ Printing to thermal printer ZJ-80...');
    console.log('📤 Getting kd_pel from server...');
    
    $.ajax({
      url: 'f_jual_cetnota.php',
      type: 'POST',
      data: { 
        action: 'get_kd_pel',
        no_fakjual: no_fakjual,
        tgl_jual: tgl_jual,
        kd_toko: kd_toko
      },
      dataType: "json",
      success: function(response) {
        var kd_pel = response.kd_pel || 'IDPEL-0';
        var d = '';
        var keyword = kd_toko + ';' + kd_pel + ';' + no_fakjual + ';' + tgl_jual + ';' + d + ';' + kd_bayar + ';' + bayar + ';' + saldohut + ';' + tgl_jt + ';' + susuk;
        
        console.log('📤 Sending print request to f_jualcetaknota.php');
        
        // Panggil f_jualcetaknota.php seperti semula
        $.ajax({
          url: 'f_jualcetaknota.php',
          type: 'POST',
          data: { keyword: keyword },
          dataType: "json",
          success: function(response) {
            console.log('✅ Print request sent successfully');
            if (typeof popnew_ok !== 'undefined') {
              popnew_ok('Nota berhasil dicetak ke printer ZJ-80');
            }
          },
          error: function(xhr, ajaxOptions, thrownError) {
            console.error('❌ Print request failed:', thrownError);
            if (typeof popnew_ok !== 'undefined') {
              popnew_ok('Gagal mencetak: ' + thrownError);
            }
          }
        });
      },
      error: function() {
        // Jika gagal ambil kd_pel, gunakan default dan langsung cetak
        var kd_pel = 'IDPEL-0';
        var d = '';
        var keyword = kd_toko + ';' + kd_pel + ';' + no_fakjual + ';' + tgl_jual + ';' + d + ';' + kd_bayar + ';' + bayar + ';' + saldohut + ';' + tgl_jt + ';' + susuk;
        
        console.log('📤 Sending print request to f_jualcetaknota.php (using default kd_pel)');
        
        $.ajax({
          url: 'f_jualcetaknota.php',
          type: 'POST',
          data: { keyword: keyword },
          dataType: "json",
          success: function(response) {
            console.log('✅ Print request sent successfully');
            if (typeof popnew_ok !== 'undefined') {
              popnew_ok('Nota berhasil dicetak ke printer ZJ-80');
            }
          },
          error: function(xhr, ajaxOptions, thrownError) {
            console.error('❌ Print request failed:', thrownError);
            if (typeof popnew_ok !== 'undefined') {
              popnew_ok('Gagal mencetak: ' + thrownError);
            }
          }
        });
      }
    });
  })();
  </script>
<?php
  $html = ob_get_contents(); 
  ob_end_clean();
  echo json_encode(array('hasil'=>$html));
?>
