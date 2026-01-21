<?php
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
  
  // Ambil data toko dan pelanggan
  $sqlcari=mysqli_query($connect,"SELECT * from toko where kd_toko='$kd_toko'");
  $datacari=mysqli_fetch_assoc($sqlcari);
  $nm_toko=$datacari['nm_toko'];
  $al_toko=$datacari['al_toko'];
  unset($sqlcari,$datacari);
  
  $sqlcari=mysqli_query($connect,"SELECT * from pelanggan where kd_pel='$kd_pel'");
  $datacari=mysqli_fetch_assoc($sqlcari);
  $nm_pel=$datacari['nm_pel'];
  $alamat=$datacari['al_pel'];
  unset($sqlcari,$datacari);
  
  mysqli_close($connect);
  
  // Buat URL untuk f_jual_cetak_sm.php (HTML print)
  $print_url_params = http_build_query([
      'nof' => implode(';', [
          $no_fakjual, $tgl_jual, $kd_toko, $kd_pel,
          $nm_toko, $al_toko,
          $nm_pel, $alamat, $kd_bayar, $bayar, $susuk,
          $disctot, $ongkir, $saldohut, $tgl_jt, $tgltime,
          $_SESSION['nm_user'], $voucher
      ])
  ]);
  $print_url = 'f_jual_cetak_sm.php?' . $print_url_params;
  
  // Response JSON dengan script yang akan dieksekusi
  header('Content-Type: application/json');
  
  // Escape script untuk JSON
  $script_content = '<script>
(function() {
  try {
    // Buat window baru untuk print nota
    var printWindow = window.open(' . json_encode($print_url) . ', "_blank", "width=300,height=600");
    
    if (!printWindow) {
      console.error("❌ Popup blocked! Please allow popups for this site.");
      alert("Popup diblokir! Silakan izinkan popup untuk situs ini.");
      return;
    }
    
    // Tunggu window selesai load, lalu coba print server lokal atau browser print
    printWindow.onload = function() {
      // Coba gunakan print server lokal jika tersedia
      var printData = {
        url: ' . json_encode($print_url) . ',
        printerName: "GP-80220(Cut) Series"
      };
      
      fetch("http://localhost:3000/print/url", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(printData),
      })
      .then(function(response) {
        if (response.ok) {
          return response.json();
        }
        throw new Error("Print server tidak tersedia");
      })
      .then(function(data) {
        console.log("✅ Print server response:", data);
        if (data.success) {
          console.log("✅ Nota dikirim ke print server lokal");
          setTimeout(function() { 
            printWindow.close(); 
          }, 500);
        } else {
          console.warn("⚠️ Print server reported failure, falling back to browser print");
          setTimeout(function() {
            printWindow.print();
            setTimeout(function() { 
              printWindow.close(); 
            }, 1000);
          }, 500);
        }
      })
      .catch(function(error) {
        console.log("🖨️ Menggunakan browser print dialog:", error.message);
        setTimeout(function() {
          printWindow.print();
          setTimeout(function() { 
            printWindow.close(); 
          }, 1000);
        }, 500);
      });
    };
    
    // Fallback jika onload tidak terpicu (untuk beberapa browser)
    setTimeout(function() {
      if (printWindow && !printWindow.closed) {
        printWindow.print();
        setTimeout(function() { 
          printWindow.close(); 
        }, 1000);
      }
    }, 2000);
    
    console.log("✅ Window print nota dibuka");
  } catch(e) {
    console.error("❌ Error opening print window:", e);
    alert("Gagal membuka window print: " + e.message);
  }
})();
</script>';
  
  echo json_encode(array('hasil'=>$script_content));
?>
