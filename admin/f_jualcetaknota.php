 
<?php
  $keyword = $_POST['keyword']; // Ambil data keyword yang dikirim dengan AJAX  
  ob_start();
  include 'config.php';
  session_start();
  $connect=opendtcek();
  $keyword=mysqli_escape_string($connect,$_POST['keyword']);
  //echo $keyword;
  $x=explode(';', $keyword);
  $kd_toko=$x[0];
  $kd_pel=$x[1];
  $no_fakjual=$x[2];
  $tgl_jual=$x[3];
  $d=$x[4];
  $kd_bayar=$x[5];
  $bayar=$x[6];
  $saldohut=$x[7];
  $tgl_jt=$x[8];
  $susuk=$x[9];

    $condensed = Chr(27) . Chr(33) . Chr(4);
    $bold1 = Chr(27) . Chr(69);
    $bold0 = Chr(27) . Chr(70);
    $italic1= Chr(27).Chr(ord("4"));
    $italic0= Chr(27).Chr(ord("5"));
    $spasiver = chr(27).chr(ord("I"));
    $fontsizebig=chr(27).chr(ord("W")).chr(ord("1"));
    $fontkecil=chr(27).chr(ord("g"));
    $superscript1=chr(27).chr(83);
    $superscript0=chr(27).chr(84); 
    $condensed1 = chr(15);
    $condensed0 = chr(18);
    $initialized = chr(27).chr(64);
    $open = chr(27).chr(112).chr(48).chr(25).chr(250);
    $cutPaper = chr(29) . "V" . chr(48) . chr(0);

    $sqlcari=mysqli_query($connect,"SELECT * from toko where kd_toko='$kd_toko'");
    $datacari=mysqli_fetch_assoc($sqlcari);
    $nm_toko=$datacari['nm_toko'];
    $al_toko=$datacari['al_toko'];
    unset($sqlcari,$datacari); 

    $sqlcari=mysqli_query($connect,"SELECT * from pelanggan where kd_pel='$kd_pel'");
    $datacari=mysqli_fetch_assoc($sqlcari);
    $nm_pel=$datacari['nm_pel'];
    unset($sqlcari,$datacari);

    $sql=mysqli_query($connect,"SELECT *,sum(dum_jual.qty_brg) AS qty_brg FROM dum_jual LEFT JOIN kemas ON dum_jual.kd_sat=kemas.no_urut WHERE dum_jual.no_fakjual='$no_fakjual' AND dum_jual.tgl_jual='$tgl_jual' AND dum_jual.kd_toko='$kd_toko' GROUP BY dum_jual.kd_brg,dum_jual.kd_sat,dum_jual.discitem,dum_jual.hrg_jual ORDER BY dum_jual.no_urut ASC");
    if(mysqli_num_rows($sql)>=1){
      $no=0;$subtot=0;$total=0;$def=1;
      // $Text  = $superscript1;
      $Text  = $open;  
      $Text .= spasistr('',$def).spasicenter($nm_toko,47)."\n";
      $Text .= spasicenter($al_toko,47+$def)."\n\n";
      $Text .= spasistr('',$def)."No.Struk ".spasistr('',5).":".spasistr($no_fakjual,20)."\n";
      $Text .= spasistr('',$def)."Tanggal  ".spasistr('',5).":".spasistr(gantitgl($tgl_jual),10)."\n";
      //$Text .= spasistr('',$def)."Pembeli  ".spasistr('',5).":".spasistr($nm_pel,10)."\n";
      $Text .= spasistr('',$def)."-----------------------------------------------\n";
      $Text .= spasistr('',$def)."No.".spasistr('',5)."Nama Barang\n";
      $Text .= spasistr('',$def).spasistr('',5).spasistr("Jml",10).spasistr("Disc%",12).spasistr("Harga",11).spasistr("SubTotal",12)."\n";
      $Text .= spasistr('',$def)."-----------------------------------------------\n"; 

      while($data=mysqli_fetch_assoc($sql)){
        $no++;
        $nm_sat=' '.$data['nm_sat1'];
        $hrg_jual=gantiti($data['hrg_jual']);

        if ($data['discitem']>0){
          $disc=$data['hrg_jual']*($data['discitem']/100);
          $subtot=round(($data['hrg_jual']-$disc)*$data['qty_brg'],0);
        }else{
          $subtot=$data['hrg_jual']*$data['qty_brg'];
        }

        $total=$total+$subtot; 
        $Text .= spasistr('',$def).spasinum($no,3).'.'.spasistr('',1).$data['nm_brg']."\n";

        $Text .= spasistr('',4+$def).spasinum($data['qty_brg'],3).spasistr($nm_sat,5).spasinum($data['discitem'],9).spasistr('',2).spasinum(gantiti($data['hrg_jual']),9).spasistr('',2).spasinum(gantiti($subtot),12)."\n";

      }
      $Text .= spasistr('',$def)."-----------------------------------------------\n"; 
      $Text .= spasistr('',7+$def).spasinum("Total",15).spasistr('',4).'Rp. '.spasinum(gantiti($total),16)."\n";      
      if($kd_bayar=="TUNAI"){
        $Text .= spasistr('',7+$def).spasinum("Uang Tunai",15).spasistr('',4).'Rp. '.spasinum(gantiti($bayar),16)."\n";
        $Text .= spasistr('',7+$def).spasinum("Kembali",15).spasistr('',4).'Rp. '.spasinum(gantiti($susuk),16)."\n";
      }else{
        $Text .= spasistr('',7+$def).spasinum("Uang Tunai",15).spasistr('',4).'Rp. '.spasinum(gantiti($bayar),16)."\n";
        $Text .= spasistr('',7+$def).spasinum("Kekurangan",15).spasistr('',4).'Rp. '.spasinum(gantiti($saldohut),16)."\n";
        $Text .= spasistr('',7+$def).spasinum("Jatuh Tempo",15).spasistr('',4).'Rp. '.spasinum(gantitgl($tgl_jt),16)."\n";
      }
      ////$Text .= spasistr('',$def).$no.spasistr('',2)."item".spasistr('',1).spasistr('Pembayaran',11).$kd_bayar."\n\n";
      $Text .= "\n";
      $Text .= spasicenter('BARANG YG.SUDAH DIBELI TDK BISA DIKEMBALIKAN',46+$def)."\n";
      // $Text .= spasicenter('*TERIMA KASIH*',47+$def)."\n\n\n\n\n\n\n\n\n\n\n";
      $Text .= spasicenter('*TERIMA KASIH*',47+$def)."\n\n\n\n\n\n";
      $Text .= $cutPaper;
      //$printer = printer_open("GP-80250N Series"); //open printer
      //$printer = printer_open("POS-80C"); //open printer
      $printer = printer_open("GP-80220(Cut) Series"); //open printer
      printer_set_option($printer,PRINTER_MODE,"RAW");
      printer_write($printer, $Text);    
      printer_close($printer);
    }

    
?>    
<?php
  $html = ob_get_contents(); // Masukan isi dari view.php ke dalam variabel $html
  ob_end_clean();
  // Buat array dengan index hasil dan value nya $html
  // Lalu konversi menjadi JSON
  echo json_encode(array('hasil'=>$html));
?>