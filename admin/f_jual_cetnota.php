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
  $kd_member = ''; // Default
  $poin_earned = 0; // Default
  $nm_member = ''; // Default
  $poin_saldo = 0; // Default
  
  // Cari kd_pel dan kd_member dari mas_jual jika ada
  $cekpel = mysqli_query($connect, "SELECT kd_pel, kd_member, poin_earned FROM mas_jual WHERE no_fakjual='$no_fakjual' AND tgl_jual='$tgl_jual' AND kd_toko='$kd_toko' LIMIT 1");
  if (mysqli_num_rows($cekpel) > 0) {
    $dtpel = mysqli_fetch_assoc($cekpel);
    $kd_pel = $dtpel['kd_pel'];
    $kd_member = isset($dtpel['kd_member']) ? $dtpel['kd_member'] : '';
    $poin_earned = isset($dtpel['poin_earned']) ? floatval($dtpel['poin_earned']) : 0;
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
  
  // Ambil data member jika ada
  if (!empty($kd_member)) {
    $sqlmember=mysqli_query($connect,"SELECT nm_member, poin FROM member WHERE kd_member='$kd_member' LIMIT 1");
    if (mysqli_num_rows($sqlmember) > 0) {
      $datamember=mysqli_fetch_assoc($sqlmember);
      $nm_member = $datamember['nm_member'];
      $poin_saldo = isset($datamember['poin']) ? floatval($datamember['poin']) : 0;
    }
    mysqli_free_result($sqlmember);
    unset($sqlmember,$datamember);
  }
  
  mysqli_close($connect);
  
  // CEK: Apakah PHP printer extension tersedia DAN apakah ini localhost?
  $isLocal = ($_SERVER['HTTP_HOST'] == 'localhost' || 
              $_SERVER['HTTP_HOST'] == '127.0.0.1' || 
              strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
              strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);
  
  $hasPrinterExtension = function_exists('printer_open');
  
  if ($isLocal && $hasPrinterExtension) {
    // MODE LOCAL: Gunakan thermal printing langsung via PHP
    $keyword = $kd_toko.';'.$kd_pel.';'.$no_fakjual.';'.$tgl_jual.';1;'.$kd_bayar.';'.$bayar.';'.$saldohut.';'.$tgl_jt.';'.$susuk;
    $_POST['keyword'] = $keyword;
    
    ob_start();
    try {
      include 'f_jualcetaknota.php';
      $thermal_success = true;
    } catch (Exception $e) {
      $thermal_success = false;
      error_log("Thermal printing error: " . $e->getMessage());
    } catch (Error $e) {
      $thermal_success = false;
      error_log("Thermal printing fatal error: " . $e->getMessage());
    }
    ob_end_clean();
    
    header('Content-Type: application/json');
    $script_content = '<script>console.log("✅ Thermal printing dilakukan langsung ke printer");</script>';
    echo json_encode(array('hasil'=>$script_content));
    
  } else {
    // MODE ONLINE: Gunakan client-side printing dengan fallback
    // Buat URL untuk HTML print
    $print_url_params = http_build_query([
        'nof' => implode(';', [
            $no_fakjual, $tgl_jual, $kd_toko, $kd_pel,
            $nm_toko, $al_toko,
            $nm_pel, $alamat, $kd_bayar, $bayar, $susuk,
            $disctot, $ongkir, $saldohut, $tgl_jt, $tgltime,
            $_SESSION['nm_user'], $voucher,
            $kd_member, $nm_member, $poin_earned, $poin_saldo
        ])
    ]);
    $print_url = 'f_jual_cetak_sm.php?' . $print_url_params;
    
    header('Content-Type: application/json');
    
    $script_content = '<script>
(function() {
  try {
    var printUrl = ' . json_encode($print_url) . ';
    var printWindow = window.open(printUrl, "_blank", "width=300,height=600");
    
    if (!printWindow) {
      alert("Popup diblokir! Silakan izinkan popup untuk situs ini.");
      return;
    }
    
    function triggerPrint() {
      // Coba print server lokal jika tersedia (untuk thermal printer)
      fetch("http://localhost:3000/print/url", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          url: printUrl,
          printerName: "GP-80220(Cut) Series"
        })
      })
      .then(function(response) {
        if (response.ok) {
          return response.json();
        }
        throw new Error("Print server tidak tersedia");
      })
      .then(function(data) {
        if (data.success) {
          console.log("✅ Nota dikirim ke print server lokal");
          setTimeout(function() { 
            if (printWindow && !printWindow.closed) {
              printWindow.close(); 
            }
          }, 500);
        } else {
          // Fallback ke browser print
          setTimeout(function() {
            if (printWindow && !printWindow.closed) {
              printWindow.print();
              setTimeout(function() { 
                if (printWindow && !printWindow.closed) {
                  printWindow.close(); 
                }
              }, 1000);
            }
          }, 500);
        }
      })
      .catch(function(error) {
        // Print server tidak tersedia, gunakan browser print (tidak tampilkan error)
        console.log("🖨️ Menggunakan browser print dialog");
        setTimeout(function() {
          if (printWindow && !printWindow.closed) {
            printWindow.print();
            setTimeout(function() { 
              if (printWindow && !printWindow.closed) {
                printWindow.close(); 
              }
            }, 1000);
          }
        }, 500);
      });
    }
    
    // Tunggu window selesai load
    if (printWindow.addEventListener) {
      printWindow.addEventListener("load", triggerPrint);
    } else if (printWindow.onload) {
      printWindow.onload = triggerPrint;
    }
    
    // Fallback timeout
    setTimeout(function() {
      if (printWindow && !printWindow.closed) {
        triggerPrint();
      }
    }, 2000);
    
  } catch(e) {
    console.error("❌ Error opening print window:", e);
  }
})();
</script>';
    
    echo json_encode(array('hasil'=>$script_content));
  }
?>
