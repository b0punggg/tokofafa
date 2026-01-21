<?php
  // Prevent direct access - must be POST request
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: text/html; charset=UTF-8');
    header('Location: f_jual.php');
    exit;
  }
  
  ob_start();
  error_reporting(0);
  ini_set('display_errors', 0);

include 'config.php';
date_default_timezone_set('Asia/Jakarta');
session_start();
$kd_toko      = $_SESSION['id_toko'];
$id_user      = $_SESSION['id_user'];
$tgl_jual     = isset($_POST['tgl_jual']) ? $_POST['tgl_jual'] : '';
$no_fakjual   = isset($_POST['no_fakjuals']) ? $_POST['no_fakjuals'] : '';
$kd_pel       = isset($_POST['kd_pel_byr']) ? $_POST['kd_pel_byr'] : '';
$kd_member    = isset($_POST['kd_member_byr']) ? $_POST['kd_member_byr'] : '';
$kd_bayar     = isset($_POST['kd_bayar']) ? $_POST['kd_bayar'] : '';
$byr_jual     = backnumdes(isset($_POST['byr_awal']) ? $_POST['byr_awal'] : '0');//**Tagihan penjualan awal
$byr_tot      = backnumdes(isset($_POST['tot_belanja']) ? $_POST['tot_belanja'] : '0');//**Tagihan awal + disc + ongkir
$bayar        = backnumdes(isset($_POST['bayar']) ? $_POST['bayar'] : '0');//dibayar sejumlah tagihan    
$susuk        = backnumdes(isset($_POST['kembali']) ? $_POST['kembali'] : '0');    
$disctot      = backnumdes(isset($_POST['disctot']) ? $_POST['disctot'] : '0'); // Discount nota
$disctotit    = backnumdes(isset($_POST['tdiscitem1']) ? $_POST['tdiscitem1'] : '0');// Discount item
$voucher      = backnumdes(isset($_POST['voucher']) ? $_POST['voucher'] : '0');// voucher
$disc_member  = backnumdes(isset($_POST['disc_member_hidden']) ? $_POST['disc_member_hidden'] : '0');// Diskon member
$ongkir       = backnumdes(isset($_POST['ongkir']) ? $_POST['ongkir'] : '0');
$tf           = isset($_POST['pil_tf']) ? $_POST['pil_tf'] : '';
$tgl_jt       = isset($_POST['tgl_jtnota']) ? $_POST['tgl_jtnota'] : '';
$pil_cetak    = isset($_POST['pil_cetak']) ? $_POST['pil_cetak'] : '';
$poin_earned  = isset($_POST['poin_earned_hidden']) ? floatval($_POST['poin_earned_hidden']) : 0; // Poin yang didapat dari transaksi
// Validasi server-side: setiap kelipatan Rp 50.000 mendapat 1 poin
$poin_earned = floor($byr_tot / 50000);
$poin_redeem  = isset($_POST['poin_redeem']) ? floatval(str_replace('.', '', $_POST['poin_redeem'])) : 0; // Jumlah poin yang ditukar
$poin_redeem_value = isset($_POST['poin_redeem_hidden']) ? floatval($_POST['poin_redeem_hidden']) : 0; // Nilai rupiah dari poin yang ditukar
$d            = false;
$tghi         = date("Y-m-d H:i:s");   
  
//echo $voucher;
$conbayar=opendtcek();              


//CARI NOURUT RETUR
$cekret=mysqli_query($conbayar,"SELECT no_urutretur FROM retur_jual WHERE no_fakjual='$no_fakjual'");
if(mysqli_num_rows($cekret)>=1){
  $dtret=mysqli_fetch_assoc($cekret);
  $no_urutretur=$dtret['no_urutretur'];
}else {
  $no_urutretur=0;
}

$copy = 1; // Default value
$nofak_awal = 1; // Default value

$sq_set=mysqli_query($conbayar,"SELECT * FROM seting");
while($dt_set=mysqli_fetch_assoc($sq_set)){
  
  if ($dt_set['nm_per']=='COPY'){
    $copy=$dt_set['kode'];  
  }
  if ($dt_set['nm_per']==$kd_toko){
    $nofak_awal=$dt_set['kode']+1;  
  }
}
mysqli_query($conbayar,"UPDATE seting SET kode='$nofak_awal' WHERE nm_per='$kd_toko'");
mysqli_free_result($sq_set);unset($dt_set);

//update simpan pada dum_jual
mysqli_query($conbayar,"UPDATE dum_jual set bayar='SUDAH',tgl_jt='$tgl_jt',kd_pel='$kd_pel',kd_bayar='$kd_bayar',trf='$tf' WHERE no_fakjual='$no_fakjual' AND tgl_jual='$tgl_jual' AND kd_toko='$kd_toko'");

$saldohut=$byr_tot-$bayar;  
if ($saldohut<0){$saldohut=0;}

if ($saldohut==0){
  $ket_bayar="LUNAS";
} else {
  $ket_bayar="BELUM";
}

//jika ada diskon total save ke dum_jual dengan prosentansi proposional;
$awaldisc=$disctot/$byr_jual; 
$disckov=$awaldisc*100;
$awaldiscvo=$voucher/$byr_jual; 
$disckovvo=$awaldiscvo*100;
$tot_discitem=0;$disc=0;$laba=0;
if($disctot>0 AND $disctotit==0){
 
  $ceksql=mysqli_query($conbayar,"SELECT * from dum_jual where no_fakjual='$no_fakjual' and tgl_jual='$tgl_jual' and kd_toko='$kd_toko' order by no_urut");
  //echo '$byr_jual='.$byr_jual;
  while($datahit=mysqli_fetch_assoc($ceksql)){
    $no_urut=$datahit['no_urut'];
    $disc=$datahit['hrg_jual']-($datahit['hrg_jual']*($disckov/100));
    $laba=($disc-$datahit['hrg_beli'])*$datahit['qty_brg'];
    $tot_discitem=$tot_discitem+(($datahit['hrg_jual']-$disc)*$datahit['qty_brg']); 
    mysqli_query($conbayar,"UPDATE dum_jual set discitem='$disckov',laba='$laba',discrp='0.00',discvo='0' WHERE no_urut='$no_urut'"); 
  }
  $tot_discitem=round($tot_discitem,2);
  unset($datahit,$ceksql);
} 

if($disctot==0 AND $disctotit>0){
   $ceksql=mysqli_query($conbayar,"SELECT * from dum_jual where no_fakjual='$no_fakjual' and tgl_jual='$tgl_jual' and kd_toko='$kd_toko'");
   $tot=0;$tot_discitem=0;
   while($datahit=mysqli_fetch_assoc($ceksql)){
     $tot=$datahit['discrp']*$datahit['qty_brg']; 
     $tot_discitem=$tot_discitem+$tot;
     $hrgnet=$datahit['hrg_jual']-$datahit['discrp']; 
     $laba=($hrgnet-$datahit['hrg_beli'])*$datahit['qty_brg'];
     $no_urut=$datahit['no_urut'];
     mysqli_query($conbayar,"UPDATE dum_jual set laba='$laba',discitem=0,discvo='0' WHERE no_urut='$no_urut'"); 
   }
   unset($datahit,$ceksql);
} 

if($disctot>0 AND $disctotit>0){
   $ceksql=mysqli_query($conbayar,"SELECT * from dum_jual where no_fakjual='$no_fakjual' and tgl_jual='$tgl_jual' and kd_toko='$kd_toko'");
   $tot_discitem=0;$x=0;$y=0;
   while($datahit=mysqli_fetch_assoc($ceksql)){
     $no_urut=$datahit['no_urut'];
     $discrp=$datahit['discrp'];
     $discitem=$datahit['hrg_jual']*$disckov/100;
     $tdisc=$datahit['hrg_jual']-($discitem+$discrp);
     $laba=($tdisc-$datahit['hrg_beli'])*$datahit['qty_brg'];

     $x=$x+($discitem*$datahit['qty_brg']);
     $y=$y+($discrp*$datahit['qty_brg']);
     $tot_discitem=$x+$y;
     mysqli_query($conbayar,"UPDATE dum_jual set laba='$laba',discitem='$disckov',discvo='0' WHERE no_urut='$no_urut'"); 
   }   
   unset($datahit,$ceksql);
} 

if($disctot==0 AND $disctotit==0){
  $tot_discitem=0;
  $ceksql=mysqli_query($conbayar,"SELECT * from dum_jual where no_fakjual='$no_fakjual' and tgl_jual='$tgl_jual' and kd_toko='$kd_toko'");
   while($datahit=mysqli_fetch_assoc($ceksql)){
     $no_urut=$datahit['no_urut'];
     $laba=($datahit['hrg_jual']-$datahit['hrg_beli'])*$datahit['qty_brg'];
     mysqli_query($conbayar,"UPDATE dum_jual set laba='$laba',discitem='0',discrp='0',discvo='0' WHERE no_urut='$no_urut'"); 
   }
   unset($datahit,$ceksql);
}  

if($voucher>0){
  $disc=0;$laba=0;$tot_discitem=0;$dicvoucer=0;$x=0;$y=0;$z=0;
  $ceksql=mysqli_query($conbayar,"SELECT * from dum_jual where no_fakjual='$no_fakjual' and tgl_jual='$tgl_jual' and kd_toko='$kd_toko' order by no_urut");
  //echo '$byr_jual='.$byr_jual;

  while($datahit=mysqli_fetch_assoc($ceksql)){
    $no_urut=$datahit['no_urut'];
    $discrp=$datahit['discrp'];
    $discitem=$datahit['hrg_jual']*$disckov/100;
    $dicvoucer=$datahit['hrg_jual']*$disckovvo/100;
    $disc=$datahit['hrg_jual']-($dicvoucer+$discitem+$discrp);
    $laba=($disc-$datahit['hrg_beli'])*$datahit['qty_brg'];
    
    mysqli_query($conbayar,"UPDATE dum_jual set discvo='$disckovvo',laba='$laba' WHERE no_urut='$no_urut'"); 
    $x=$x+($discitem*$datahit['qty_brg']);
    $y=$y+($discrp*$datahit['qty_brg']);
    $z=$z+($dicvoucer*$datahit['qty_brg']);
    $tot_discitem=$x+$y+$z;
  }
  $tot_discitem=round($tot_discitem,2);
  unset($datahit,$ceksql);
}
//------------
// Tambahkan diskon member ke tot_discitem
$tot_discitem = $tot_discitem + $disc_member;
$tot_discitem = round($tot_discitem, 2);
//echo $tot_discitem;

//ambil tanggal 
$totlaba=0;$tot_sale=0;
$connect1 = opendtcek();
$ceksql=mysqli_query($connect1,"SELECT * from dum_jual where no_fakjual='$no_fakjual' and tgl_jual='$tgl_jual' and kd_toko='$kd_toko' order by no_urut");
while($datahit=mysqli_fetch_assoc($ceksql)){
 // $tgl_jual=$datahit['tgl_jual'];
 $tgl_jth=gantitglsave($datahit['tgl_jt']);
 $no_urut=$datahit['no_urut'];
 $totlaba=$totlaba+$datahit['laba'];

 //untuk mas_jual tot_jual harus sebelum disckon
 $tot_sale=$tot_sale+($datahit['hrg_jual']*$datahit['qty_brg']);

 mysqli_query($connect1,"UPDATE dum_jual set bayar='SUDAH',panding=false,kd_pel='$kd_pel',kd_bayar='$kd_bayar',trf='$tf' WHERE no_urut='$no_urut'");
}	
mysqli_close($connect1);
unset($datahit);unset($ceksql);
//---------------------------------

$update=0;$d=false;        
$defmodal=($tot_sale-$tot_discitem)-$totlaba; 
$cek=mysqli_query($conbayar,"SELECT * FROM mas_jual WHERE kd_toko='$kd_toko' AND no_fakjual='$no_fakjual'");

//ke atas ok

if(mysqli_num_rows($cek)>=1){ //** data ditemukan
  $data=mysqli_fetch_assoc($cek);  
  $no_urutx=$data['no_urut'];
  $simpandt=opendtcek();
  $d=mysqli_query($simpandt,"UPDATE mas_jual set tot_jual='$tot_sale',tot_disc='$tot_discitem',tot_laba='$totlaba',ket_bayar='$ket_bayar',kd_bayar='$kd_bayar',bayar_uang='$bayar',susuk_uang='$susuk',cetak='0',ongkir='$ongkir',saldo_hutang='$saldohut',tgl_jt='$tgl_jt',kd_pel='$kd_pel',trf='$tf' WHERE no_urut='$no_urutx'");
  unset($data);

  if ($kd_bayar=='TEMPO'){
    //**CEK DAHULU PADA MAS_JUAL_HUTANG apakah angsuran TEMPO/TUNAI
    $carih=mysqli_query($simpandt,"SELECT * FROM mas_jual_hutang WHERE no_fakjual='$no_fakjual' and tgl_jual='$tgl_jual' and kd_toko='$kd_toko' ORDER BY no_urut ASC LIMIT 1");
    if(mysqli_num_rows($carih)>0){
      $dat=mysqli_fetch_assoc($carih);
      $no_uruts=$dat['no_urut'];
      $d=mysqli_query($simpandt,"UPDATE mas_jual_hutang SET tgl_jt='$tgl_jt',totjual='$byr_tot',saldo_awal='$byr_tot',byr_hutang='$bayar',
      saldo_hutang='$saldohut',ket='$ket_bayar',kd_pel='$kd_pel',trf='$tf' WHERE no_urut='$no_uruts'"); 
    }
    unset($carih,$dat);

    $carihut=mysqli_query($conbayar,"SELECT * from mas_jual_hutang WHERE no_fakjual='$no_fakjual' and tgl_jual='$tgl_jual' and kd_toko='$kd_toko' ORDER BY no_urut ASC");
    if (mysqli_num_rows($carihut)>=1) {
      //echo 'ketemu';
      //**Jika sudah ada pembayaran harus UPDATE pembayaran
      // cek apakah tagihan total > pembayaran angsuran yg sdh dilakukan    
        $x=0;$saldoawal=0;$bayard=0;$byrmasuk=0;$modal=0;$setor=0;$totmodal=0; 
        
        if ($saldohut<0){$saldohut=0;}

        if ($saldohut==0){
          $ket_bayar="LUNAS";
        } else {
          $ket_bayar="BELUM";
        }
        $saldoawal=$saldohut;
        while ($dcarihut=mysqli_fetch_assoc($carihut)){
          $setor    = $setor+$dcarihut['byr_hutang'];
          $no_urut  = $dcarihut['no_urut'];
          if($setor <= $defmodal){
              $modalmsk = $dcarihut['byr_hutang'];
              $labamsk  = 0;
              $totmodal = $totmodal+$modalmsk; 
              // echo '$setor'.$setor.'<br>';
              // echo '$defmodal'.$defmodal.'<br>';
              // echo '$modalsmk'.$modalmsk.'<br>';
          }else{   
              if($totmodal <= $defmodal){
                  $modalmsk = $defmodal-$totmodal;
                  $labamsk  = $dcarihut['byr_hutang']-$modalmsk;
              }else{
                  $modalmsk = 0;
                  $labamsk  = $dcarihut['byr_hutang'];
              }
              $totmodal = $totmodal+$modalmsk;
              // echo '$setor'.$setor.'<br>';
              // echo '$defmodal'.$defmodal.'<br>';
              // echo '$modalsmk'.$modalmsk.'<br>';
          }
          $x++;
          
          if ($x==1){
            $d=mysqli_query($conbayar,"UPDATE mas_jual_hutang SET tgl_jt='$tgl_jt',totjual='$byr_tot',saldo_awal='$byr_tot',byr_hutang='$bayar',
            saldo_hutang='$saldohut',ket='$ket_bayar',kd_pel='$kd_pel',modal='$modalmsk',laba='$labamsk',trf='$tf' WHERE no_urut='$no_urut'"); 
            $d=mysqli_query($conbayar,"UPDATE mas_jual SET ket_bayar='$ket_bayar',saldo_hutang='$saldohut',trf='$tf' WHERE no_fakjual='$no_fakjual'"); 

            $xx=mysqli_query($conbayar,"SELECT SUM(modal) as jmodal FROM mas_jual_hutang WHERE no_fakjual='$no_fakjual'");
            $dtr=mysqli_fetch_assoc($xx);
            $modal    = $dtr['jmodal'];
            unset($xx,$dtr);
          } else {    
            $bayard=$dcarihut['byr_hutang'];
            
            $sld_hut=$saldoawal-$bayard;  
            $d=mysqli_query($conbayar,"UPDATE mas_jual_hutang SET tgl_jt='$tgl_jt',totjual='$byr_tot',saldo_awal='$saldoawal',byr_hutang='$bayard',saldo_hutang='$sld_hut',ket='$ket_bayar',kd_pel='$kd_pel',modal='$modalmsk',laba='$labamsk',trf='$tf' WHERE no_urut='$no_urut'");    
            $d=mysqli_query($conbayar,"UPDATE mas_jual SET ket_bayar='$ket_bayar',saldo_hutang='$sld_hut',trf='$tf' WHERE no_fakjual='$no_fakjual'"); 

            $saldoawal=$saldoawal-$bayard;
          }
          
        }
      $update=1;  
      $simpanup='simpanup;'.$no_fakjual;  
    } else {
      // jika tidak ditemukan insert data baru
      $saldohut=$byr_tot-$bayar;
      if($defmodal>=$bayar){
        $modalmsk=$bayar;
        $labamsk =0;
      }else{
        $modalmsk=$defmodal;
        $labamsk =$bayar-$defmodal;
      }  
      $d=mysqli_query($conbayar,"INSERT INTO mas_jual_hutang values('','$kd_pel','$no_fakjual','$tgl_jual','$tgl_jual','$byr_tot','$byr_tot','$bayar','$saldohut','$ket_bayar','$kd_toko','$tgl_jt','$modalmsk','$labamsk','$tf','$no_urutretur')");
      $d=mysqli_query($conbayar,"INSERT INTO mas_jual values('','$tgl_jual','$kd_toko','$no_fakjual','$tot_sale','$tot_discitem','$totlaba','$ket_bayar','$kd_bayar','$bayar','$susuk','$kd_pel','$kd_member','$poin_earned','$ongkir','$saldohut','$tgl_jt','$tf','$tghi')");
      
      // Proses poin: tambah poin yang didapat dan kurangi poin yang ditukar
      if($kd_member != ''){
        // Kurangi poin yang ditukar terlebih dahulu
        if($poin_redeem > 0){
          mysqli_query($conbayar,"UPDATE member SET poin = poin - $poin_redeem WHERE kd_member='$kd_member'");
          $cekpoin = mysqli_query($conbayar,"SELECT poin FROM member WHERE kd_member='$kd_member'");
          $datapoin = mysqli_fetch_assoc($cekpoin);
          $poin_saldo = isset($datapoin['poin']) ? $datapoin['poin'] : 0;
          mysqli_query($conbayar,"INSERT INTO member_poin_history values('','$kd_member','$no_fakjual','$tgl_jual','0','$poin_redeem','$poin_saldo','Penukaran Poin Transaksi - $no_fakjual','$kd_toko','$tghi')");
          mysqli_free_result($cekpoin);
          unset($datapoin);
        }
        // Tambah poin yang didapat dari transaksi
        if($poin_earned > 0){
          mysqli_query($conbayar,"UPDATE member SET poin = poin + $poin_earned WHERE kd_member='$kd_member'");
          $cekpoin = mysqli_query($conbayar,"SELECT poin FROM member WHERE kd_member='$kd_member'");
          $datapoin = mysqli_fetch_assoc($cekpoin);
          $poin_saldo = isset($datapoin['poin']) ? $datapoin['poin'] : 0;
          mysqli_query($conbayar,"INSERT INTO member_poin_history values('','$kd_member','$no_fakjual','$tgl_jual','$poin_earned','0','$poin_saldo','Transaksi Penjualan - $no_fakjual','$kd_toko','$tghi')");
          mysqli_free_result($cekpoin);
          unset($datapoin);
        }
      }    
    }  
  } else {
    // jika edit menjadi cash hapus data lama
    $d=mysqli_query($conbayar,"DELETE FROM mas_jual_hutang WHERE no_fakjual='$no_fakjual' and tgl_jual='$tgl_jual' and kd_toko='$kd_toko'");
  }     
  mysqli_close($simpandt);
}if(mysqli_num_rows($cek)>=1){ //** data ditemukan
  $data=mysqli_fetch_assoc($cek);  
  $no_urutx=$data['no_urut'];
  $simpandt=opendtcek();
  $d=mysqli_query($simpandt,"UPDATE mas_jual set tot_jual='$tot_sale',tot_disc='$tot_discitem',tot_laba='$totlaba',ket_bayar='$ket_bayar',kd_bayar='$kd_bayar',bayar_uang='$bayar',susuk_uang='$susuk',cetak='0',ongkir='$ongkir',saldo_hutang='$saldohut',tgl_jt='$tgl_jt',kd_pel='$kd_pel',trf='$tf' WHERE no_urut='$no_urutx'");
  unset($data);

  if ($kd_bayar=='TEMPO'){
    //**CEK DAHULU PADA MAS_JUAL_HUTANG apakah angsuran TEMPO/TUNAI
    $carih=mysqli_query($simpandt,"SELECT * FROM mas_jual_hutang WHERE no_fakjual='$no_fakjual' and tgl_jual='$tgl_jual' and kd_toko='$kd_toko' ORDER BY no_urut ASC LIMIT 1");
    if(mysqli_num_rows($carih)>0){
      $dat=mysqli_fetch_assoc($carih);
      $no_uruts=$dat['no_urut'];
      $d=mysqli_query($simpandt,"UPDATE mas_jual_hutang SET tgl_jt='$tgl_jt',totjual='$byr_tot',saldo_awal='$byr_tot',byr_hutang='$bayar',
      saldo_hutang='$saldohut',ket='$ket_bayar',kd_pel='$kd_pel',trf='$tf' WHERE no_urut='$no_uruts'"); 
    }
    unset($carih,$dat);

    $carihut=mysqli_query($conbayar,"SELECT * from mas_jual_hutang WHERE no_fakjual='$no_fakjual' and tgl_jual='$tgl_jual' and kd_toko='$kd_toko' ORDER BY no_urut ASC");
    if (mysqli_num_rows($carihut)>=1) {
      //echo 'ketemu';
      //**Jika sudah ada pembayaran harus UPDATE pembayaran
      // cek apakah tagihan total > pembayaran angsuran yg sdh dilakukan    
        $x=0;$saldoawal=0;$bayard=0;$byrmasuk=0;$modal=0;$setor=0;$totmodal=0; 
        
        if ($saldohut<0){$saldohut=0;}

        if ($saldohut==0){
          $ket_bayar="LUNAS";
        } else {
          $ket_bayar="BELUM";
        }
        $saldoawal=$saldohut;
        while ($dcarihut=mysqli_fetch_assoc($carihut)){
          $setor    = $setor+$dcarihut['byr_hutang'];
          $no_urut  = $dcarihut['no_urut'];
          if($setor <= $defmodal){
              $modalmsk = $dcarihut['byr_hutang'];
              $labamsk  = 0;
              $totmodal = $totmodal+$modalmsk; 
              // echo '$setor'.$setor.'<br>';
              // echo '$defmodal'.$defmodal.'<br>';
              // echo '$modalsmk'.$modalmsk.'<br>';
          }else{   
              if($totmodal <= $defmodal){
                  $modalmsk = $defmodal-$totmodal;
                  $labamsk  = $dcarihut['byr_hutang']-$modalmsk;
              }else{
                  $modalmsk = 0;
                  $labamsk  = $dcarihut['byr_hutang'];
              }
              $totmodal = $totmodal+$modalmsk;
              // echo '$setor'.$setor.'<br>';
              // echo '$defmodal'.$defmodal.'<br>';
              // echo '$modalsmk'.$modalmsk.'<br>';
          }
          $x++;
          
          if ($x==1){
            $d=mysqli_query($conbayar,"UPDATE mas_jual_hutang SET tgl_jt='$tgl_jt',totjual='$byr_tot',saldo_awal='$byr_tot',byr_hutang='$bayar',
            saldo_hutang='$saldohut',ket='$ket_bayar',kd_pel='$kd_pel',modal='$modalmsk',laba='$labamsk',trf='$tf' WHERE no_urut='$no_urut'"); 
            $d=mysqli_query($conbayar,"UPDATE mas_jual SET ket_bayar='$ket_bayar',saldo_hutang='$saldohut',trf='$tf' WHERE no_fakjual='$no_fakjual'"); 

            $xx=mysqli_query($conbayar,"SELECT SUM(modal) as jmodal FROM mas_jual_hutang WHERE no_fakjual='$no_fakjual'");
            $dtr=mysqli_fetch_assoc($xx);
            $modal    = $dtr['jmodal'];
            unset($xx,$dtr);
          } else {    
            $bayard=$dcarihut['byr_hutang'];
            
            $sld_hut=$saldoawal-$bayard;  
            $d=mysqli_query($conbayar,"UPDATE mas_jual_hutang SET tgl_jt='$tgl_jt',totjual='$byr_tot',saldo_awal='$saldoawal',byr_hutang='$bayard',saldo_hutang='$sld_hut',ket='$ket_bayar',kd_pel='$kd_pel',modal='$modalmsk',laba='$labamsk',trf='$tf' WHERE no_urut='$no_urut'");    
            $d=mysqli_query($conbayar,"UPDATE mas_jual SET ket_bayar='$ket_bayar',saldo_hutang='$sld_hut',trf='$tf' WHERE no_fakjual='$no_fakjual'"); 

            $saldoawal=$saldoawal-$bayard;
          }
          
        }
      $update=1;  
      $simpanup='simpanup;'.$no_fakjual;  
    } else {
      // jika tidak ditemukan insert data baru
      $saldohut=$byr_tot-$bayar;
      if($defmodal>=$bayar){
        $modalmsk=$bayar;
        $labamsk =0;
      }else{
        $modalmsk=$defmodal;
        $labamsk =$bayar-$defmodal;
      }  
      $d=mysqli_query($conbayar,"INSERT INTO mas_jual_hutang values('','$kd_pel','$no_fakjual','$tgl_jual','$tgl_jual','$byr_tot','$byr_tot','$bayar','$saldohut','$ket_bayar','$kd_toko','$tgl_jt','$modalmsk','$labamsk','$tf','$no_urutretur')");
      $d=mysqli_query($conbayar,"INSERT INTO mas_jual values('','$tgl_jual','$kd_toko','$no_fakjual','$tot_sale','$tot_discitem','$totlaba','$ket_bayar','$kd_bayar','$bayar','$susuk','$kd_pel','$kd_member','$poin_earned','$ongkir','$saldohut','$tgl_jt','$tf','$tghi')");
      
      // Proses poin: tambah poin yang didapat dan kurangi poin yang ditukar
      if($kd_member != ''){
        // Kurangi poin yang ditukar terlebih dahulu
        if($poin_redeem > 0){
          mysqli_query($conbayar,"UPDATE member SET poin = poin - $poin_redeem WHERE kd_member='$kd_member'");
          $cekpoin = mysqli_query($conbayar,"SELECT poin FROM member WHERE kd_member='$kd_member'");
          $datapoin = mysqli_fetch_assoc($cekpoin);
          $poin_saldo = isset($datapoin['poin']) ? $datapoin['poin'] : 0;
          mysqli_query($conbayar,"INSERT INTO member_poin_history values('','$kd_member','$no_fakjual','$tgl_jual','0','$poin_redeem','$poin_saldo','Penukaran Poin Transaksi - $no_fakjual','$kd_toko','$tghi')");
          mysqli_free_result($cekpoin);
          unset($datapoin);
        }
        // Tambah poin yang didapat dari transaksi
        if($poin_earned > 0){
          mysqli_query($conbayar,"UPDATE member SET poin = poin + $poin_earned WHERE kd_member='$kd_member'");
          $cekpoin = mysqli_query($conbayar,"SELECT poin FROM member WHERE kd_member='$kd_member'");
          $datapoin = mysqli_fetch_assoc($cekpoin);
          $poin_saldo = isset($datapoin['poin']) ? $datapoin['poin'] : 0;
          mysqli_query($conbayar,"INSERT INTO member_poin_history values('','$kd_member','$no_fakjual','$tgl_jual','$poin_earned','0','$poin_saldo','Transaksi Penjualan - $no_fakjual','$kd_toko','$tghi')");
          mysqli_free_result($cekpoin);
          unset($datapoin);
        }
      }    
    }  
  } else {
    // jika edit menjadi cash hapus data lama
    $d=mysqli_query($conbayar,"DELETE FROM mas_jual_hutang WHERE no_fakjual='$no_fakjual' and tgl_jual='$tgl_jual' and kd_toko='$kd_toko'");
  }     
  mysqli_close($simpandt);
} else {
  $saldohut=$byr_tot-$bayar;
  if ($saldohut<0){$saldohut=0;}
  if ($saldohut==0){
    $ket_bayar="LUNAS";
  } else {
    $ket_bayar="BELUM";
  }
  $d=mysqli_query($conbayar,"INSERT INTO mas_jual values('','$tgl_jual','$kd_toko','$no_fakjual','$tot_sale','$tot_discitem','$totlaba','$ket_bayar','$kd_bayar','$bayar','$susuk','$kd_pel','$kd_member','$poin_earned','$ongkir','$saldohut','$tgl_jt','$tf','$tghi')");

  // Proses poin: tambah poin yang didapat dan kurangi poin yang ditukar
  if($kd_member != ''){
    // Kurangi poin yang ditukar terlebih dahulu
    if($poin_redeem > 0){
      mysqli_query($conbayar,"UPDATE member SET poin = poin - $poin_redeem WHERE kd_member='$kd_member'");
      $cekpoin = mysqli_query($conbayar,"SELECT poin FROM member WHERE kd_member='$kd_member'");
      $datapoin = mysqli_fetch_assoc($cekpoin);
      $poin_saldo = isset($datapoin['poin']) ? $datapoin['poin'] : 0;
      mysqli_query($conbayar,"INSERT INTO member_poin_history values('','$kd_member','$no_fakjual','$tgl_jual','0','$poin_redeem','$poin_saldo','Penukaran Poin Transaksi - $no_fakjual','$kd_toko','$tghi')");
      mysqli_free_result($cekpoin);
      unset($datapoin);
    }
    // Tambah poin yang didapat dari transaksi
    if($poin_earned > 0){
      mysqli_query($conbayar,"UPDATE member SET poin = poin + $poin_earned WHERE kd_member='$kd_member'");
      $cekpoin = mysqli_query($conbayar,"SELECT poin FROM member WHERE kd_member='$kd_member'");
      $datapoin = mysqli_fetch_assoc($cekpoin);
      $poin_saldo = isset($datapoin['poin']) ? $datapoin['poin'] : 0;
      mysqli_query($conbayar,"INSERT INTO member_poin_history values('','$kd_member','$no_fakjual','$tgl_jual','$poin_earned','0','$poin_saldo','Transaksi Penjualan - $no_fakjual','$kd_toko','$tghi')");
      mysqli_free_result($cekpoin);
      unset($datapoin);
    }
  }

  if($kd_bayar=='TEMPO'){
    // echo 'if...' .$defmodal.' '.$bayar.'<br>';
    if($defmodal>=$bayar){
      $modalmsk=$bayar;
      $labamsk =0;
    }else{
      $modalmsk=$defmodal;
      $labamsk =$bayar-$defmodal;
    }
    $d=mysqli_query($conbayar,"INSERT INTO mas_jual_hutang VALUES('','$kd_pel','$no_fakjual','$tgl_jual','$tgl_jual','$byr_tot','$byr_tot','$bayar','$saldohut','$ket_bayar','$kd_toko','$tgl_jt','$modalmsk','$labamsk','$tf','$no_urutretur')");
  }
}
unset($cek);
//bawah ok

if ($pil_cetak =="CETAK"){
  $open = chr(27).chr(112).chr(48).chr(25).chr(250); //open cash drawer
  $cutPaper = chr(29) . "V" . chr(48) . chr(0); //cut paper

  $sqlcari=mysqli_query($conbayar,"SELECT * from toko where kd_toko='$kd_toko'");
  $datacari=mysqli_fetch_assoc($sqlcari);
  $nm_toko=$datacari['nm_toko'];
  
  $al_toko=$datacari['al_toko'];
  unset($sqlcari,$datacari); 

  $sqlcari=mysqli_query($conbayar,"SELECT * from pelanggan where kd_pel='$kd_pel'");
  $datacari=mysqli_fetch_assoc($sqlcari);
  $nm_pel=$datacari['nm_pel'];
  $alamat=$datacari['al_pel'];
  unset($sqlcari,$datacari);

  $sqlcari=mysqli_query($conbayar,"SELECT execut FROM mas_jual WHERE no_fakjual='$no_fakjual' and tgl_jual='$tgl_jual' AND kd_toko='$kd_toko'");
  $datacari=mysqli_fetch_assoc($sqlcari);
  
  $dd=explode(" ",$datacari['execut']);
  $tgltime=gantitgl($tgl_jual)." ".$dd[1];
  unset($sqlcari,$datacari);
//   if($id_user=='23'){
    $bayar=gantiti(round($bayar,0));$susuk=gantiti(round($susuk,0));
    $saldohut=gantiti(round($saldohut,0));$ongkir=round($ongkir,0);$disctot=round($disctot,0);$voucher=round($voucher,0);
    $dtc=$no_fakjual.';'.$tgl_jual.';'.$kd_toko.';'.$nm_pel.';'.$alamat.';'.$tgltime.';'.$disctot.';'.$voucher.';'.$ongkir.';'.$kd_bayar.';'.$bayar.';'.$susuk.';'.$saldohut.';'.gantitgl($tgl_jt);
    ?><script type="text/javascript">cetaknota('<?=$dtc?>','<?=$copy?>')</script><?php  
//   }else{
    // $sql=mysqli_query($conbayar,"SELECT *,sum(dum_jual.qty_brg) AS qty_brg FROM dum_jual LEFT JOIN kemas ON dum_jual.kd_sat=kemas.no_urut WHERE dum_jual.no_fakjual='$no_fakjual' AND dum_jual.tgl_jual='$tgl_jual' AND dum_jual.kd_toko='$kd_toko' GROUP BY dum_jual.kd_brg,dum_jual.kd_sat,dum_jual.discrp,dum_jual.hrg_jual ORDER BY dum_jual.no_urut ASC");
    
    //   if(mysqli_num_rows($sql)>=1){
    //     $bayar=round($bayar,0);$susuk=round($susuk);
    //     $saldohut=round($saldohut,0);$ongkir=round($ongkir,0);$disctot=round($disctot,0);
    //     $no=0;$subtot=0;$total=0;$def=1;$ypos=145;$gdisc1=0; 
    
    //     //initial printer
    //     for ($kali=0; $kali < $copy ; $kali++) {           
    //       ?> 
            
    //         <script type="text/javascript">
    //           var printer = new Recta('2158190222', '1811')
    //           printer.open().then(function () {
    //               printer.align('center')
    //               .mode('A', true, true, true, false)
    //               .text('F A F A')
    //               .mode('A', false, false, true, false)
    //               .text('Fashion & Galery')
    //               .mode('A', false, false, false, false)
    //               .text('Komplek Pasar Ngrompak Jatisrono')
    //               .raw('\n')
    //               .print()
    //               .reset()
    //           })
              
    //           printer.open().then(function () {
    //             printer.align('left')
    //               .text('Struk   : <?=$no_fakjual?>')
    //               .text('Tanggal : <?=$tgltime?>')
    //               .print()
    //           })
    //           if ('<?=$kd_pel?>' != 'IDPEL-0'){
    //             printer.open().then(function () {
    //               printer.align('left')
    //                 .text('Pembeli : <?=$nm_pel?>')
    //                 .text('Alamat  : <?=$alamat?>')
    //                 .print()
    //             })
    //           } else {
    //             printer.open().then(function () {
    //               printer.align('left')
    //                 .text('Pembeli : <?=$nm_pel?>')
    //                 .print()
    //             })
    //           }
    //           printer.open().then(function () {
    //             printer.align('left')
    //               .text('------------------------------------------------')
    //               .text('No.   Nama Barang')
    //               .text('       Qty         Harga      Disc      SubTotal')
    //               .text('------------------------------------------------')
    //               .print()
                 
    //           })  
           
    //           <?php
    //           while($data=mysqli_fetch_assoc($sql)){
    //               $no++;
    //               $nm_sat=' '.$data['nm_sat1'];
    //               $hrg_jual=round($data['hrg_jual'],0);
                  
    //               if ($data['discrp']>0){
    //                 $subtot=($data['hrg_jual']-$data['discrp'])*$data['qty_brg'];
    //               }else{
    //                 $subtot=$data['hrg_jual']*$data['qty_brg'];
    //               }
          
    //               $qty_brg=gantitides($data['qty_brg']);   
    //               $discrp=round($data['discrp'],0);
    //               $hrg_jual=round($data['hrg_jual'],0);
    //               $total=$total+round($subtot,0); 
    //               $total=round($total,0);
    //               $subtot=round($subtot,0);
    //             ?>  
                
    //               printer.open().then(function () {
    //               printer.align('left')
    //                 .text('<?=spasinum($no.'.',4).spasistr(" ",1).spasistr($data['nm_brg'],30)?>')
    //                 .text('<?=spasistr(" ",4).spasistr($qty_brg.strtolower($nm_sat),10)." ".spasinum(gantiti($hrg_jual),12)." ".spasinum(gantiti($discrp),7)." ".spasinum(gantiti($subtot),12)?>')
    //                 .print()
    //               })
               
    //             <?php
    //           } // while 
    //           unset($data);
    //           ?>
    //              printer.open().then(function () {
    //               printer.align('left')
    //               .text('------------------------------------------------')
    //               .text('<?=spasistr(" ",16).spasistr("Total",12).spasistr(" ",2).spasistr('Rp.',3).spasistr(" ",2).spasinum(gantiti($total),13)?>') 
    //               .print()
    //             })
    
    //             if ('<?=$disctot?>' > 0){
    //               printer.open().then(function () {
    //                 printer.align('left')
    //                 .text('<?=spasistr(" ",16).spasistr("Disc nota",12).spasistr(" ",2).spasistr('Rp.',3).spasistr(" ",2).spasinum(gantiti($disctot),13)?>') 
    //                 .print()
    //               })
    //             }
                
    //             if ('<?=$voucher?>' > 0){
    //               printer.open().then(function () {
    //                 printer.align('left')
    //                 .text('<?=spasistr(" ",16).spasistr("Disc Voucher",12).spasistr(" ",2).spasistr('Rp.',3).spasistr(" ",2).spasinum(gantiti(round($voucher,0)),13)?>') 
    //                 .print()
    //               })
    //             }
                
    //             if ('<?=$ongkir?>' > 0){
    //               printer.open().then(function () {
    //                 printer.align('left')
    //                 .text('<?=spasistr(" ",16).spasistr("Ongkir",12).spasistr(" ",2).spasistr('Rp.',3).spasistr(" ",2).spasinum(gantiti($ongkir),13)?>') 
    //                 .print()
    //               })
    //             }    
                
    //             if('<?=$kd_bayar?>' == 'TUNAI'){
    //               printer.open().then(function () {
    //                 printer.align('left')
    //                 .text('<?=spasistr(" ",16).spasistr("Uang Tunai",12).spasistr(" ",2).spasistr('Rp.',3).spasistr(" ",2).spasinum(gantiti($bayar),13)?>')
    //                 .text('<?=spasistr(" ",16).spasistr("Kembali",12).spasistr(" ",2).spasistr('Rp.',3).spasistr(" ",2).spasinum(gantiti($susuk),13)?>')
    //                 .print()
    //               })
    //             } else {
    //               printer.open().then(function () {
    //                 printer.align('left')
    //                   .text('<?=spasistr(" ",16).spasistr("Uang Tunai",12).spasistr(" ",2).spasistr('Rp.',3).spasistr(" ",2).spasinum(gantiti($bayar),13)?>')
    //                   .text('<?=spasistr(" ",16).spasistr("Kekurangan",12).spasistr(" ",2).spasistr('Rp.',3).spasistr(" ",2).spasinum(gantiti($saldohut),13)?>')
    //                   .text('<?=spasistr(" ",16).spasistr("Jatuh Tempo",12).spasistr(" ",2).spasistr('Rp.',3).spasistr(" ",2).spasinum(gantitgl($tgl_jt),13)?>')
    //                 .print()
    //               })
                  
    //             }  
                 
    //             printer.open().then(function () {
    //               printer.align('center')
    //               .raw('\n')
    //               .text('TERIMA KASIH ATAS KUNJUNGAN ANDA')
    //               .feed(4)
    //               .cut(false,1)
    //               .print()
    //               .flush()
    //             })
    //           </script>    
    //         <?php
    //     }
    //   }  
      
//   }
}

if ($pil_cetak =="CETAK-CK"){

  $open = chr(27).chr(112).chr(48).chr(25).chr(250); //open cash drawer
  $cutPaper = chr(29) . "V" . chr(48) . chr(0); //cut paper

  $sqlcari=mysqli_query($conbayar,"SELECT * from toko where kd_toko='$kd_toko'");
  $datacari=mysqli_fetch_assoc($sqlcari);
  $nm_toko=$datacari['nm_toko'];
  
  $al_toko=$datacari['al_toko'];
  unset($sqlcari,$datacari); 

  $sqlcari=mysqli_query($conbayar,"SELECT * from pelanggan where kd_pel='$kd_pel'");
  $datacari=mysqli_fetch_assoc($sqlcari);
  $nm_pel=$datacari['nm_pel'];
  $alamat=$datacari['al_pel'];
  unset($sqlcari,$datacari);

  $sqlcari=mysqli_query($conbayar,"SELECT execut FROM mas_jual WHERE no_fakjual='$no_fakjual' and tgl_jual='$tgl_jual' AND kd_toko='$kd_toko'");
  $datacari=mysqli_fetch_assoc($sqlcari);
  
  $dd=explode(" ",$datacari['execut']);
  $tgltime=gantitgl($tgl_jual)." ".$dd[1];
  unset($sqlcari,$datacari);
//   if($id_user=="13"){
   $bayar=gantiti(round($bayar,0));$susuk=gantiti(round($susuk,0));
    $saldohut=gantiti(round($saldohut,0));$ongkir=round($ongkir,0);$disctot=round($disctot,0);$voucher=round($voucher,0);
    $dtc=$no_fakjual.';'.$tgl_jual.';'.$kd_toko.';'.$nm_pel.';'.$alamat.';'.$tgltime.';'.$disctot.';'.$voucher.';'.$ongkir.';'.$kd_bayar.';'.$bayar.';'.$susuk.';'.$saldohut.';'.gantitgl($tgl_jt);
    ?><script type="text/javascript">cetaknota('<?=$dtc?>','<?=$copy?>')</script><?php  
      
//   }else{
      
//   $sql=mysqli_query($conbayar,"SELECT *,sum(dum_jual.qty_brg) AS qty_brg FROM dum_jual LEFT JOIN kemas ON dum_jual.kd_sat=kemas.no_urut WHERE dum_jual.no_fakjual='$no_fakjual' AND dum_jual.tgl_jual='$tgl_jual' AND dum_jual.kd_toko='$kd_toko' GROUP BY dum_jual.kd_brg,dum_jual.kd_sat,dum_jual.discrp,dum_jual.hrg_jual ORDER BY dum_jual.no_urut ASC");
  
//   $bayar=round($bayar,0);$susuk=round($susuk);
//   $saldohut=round($saldohut,0);$ongkir=round($ongkir,0);$disctot=round($disctot,0);
//   $no=0;$subtot=0;$total=0;$def=1;$ypos=145;$gdisc1=0; 
    
//   if(mysqli_num_rows($sql)>=1){
//     //initial printer
//     for ($kali=0; $kali < $copy ; $kali++) { ?> 
//       <script type="text/javascript">
//         var printer = new Recta('2158190222', '1811')
//         printer.open().then(function () {
//           printer.align('center')
//           .mode('A', true, true, true, false)
//           .text('F A F A')
//           .mode('A', false, true, false, false)
//           .text('Fashion & Galery')
//           .mode('A', false, false, false, false)
//           .text('Kios Pasar Ngrompak Jatisrono')
//           .raw('\n')
//           .print()
//           .reset()  
//         })
        
//         printer.open().then(function () {
//           printer.align('left')
//             .text('Struk   : <?=$no_fakjual?>')
//             .text('Tanggal : <?=$tgltime?>')
//             .print()
//         })
//         if ('<?=$kd_pel?>' != 'IDPEL-0'){
//           printer.open().then(function () {
//             printer.align('left')
//               .text('Pembeli : <?=$nm_pel?>')
//               .text('Alamat  : <?=$alamat?>')
//               .print()
//           })
//         } else {
//           printer.open().then(function () {
//             printer.align('left')
//               .text('Pembeli : <?=$nm_pel?>')
//               .print()
//           })
//         }
//         printer.open().then(function () {
//           printer.align('left')
//             .text('--------------------------------')
//             .text('No.  Nama Barang')
//             .text(' Jml     Harga   Disc   SubTotal')
//             .text('--------------------------------')
//             .print()
//         }) <?php
//         while($data=mysqli_fetch_assoc($sql)){
//             $no++;
//             $nm_sat=' '.$data['nm_sat1'];
//             $hrg_jual=round($data['hrg_jual'],0);
            
//             if ($data['discrp']>0){
//               $subtot=($data['hrg_jual']-$data['discrp'])*$data['qty_brg'];
//             }else{
//               $subtot=$data['hrg_jual']*$data['qty_brg'];
//             }
    
//             $qty_brg=gantitides($data['qty_brg']);   
//             $discrp=round($data['discrp'],0);
//             $hrg_jual=round($data['hrg_jual'],0);
//             $total=$total+round($subtot,0); 
//             $total=round($total,0);
//             $subtot=round($subtot,0);
//           ?>  
          
//               printer.open().then(function () {
//               printer.align('left')
//               .text('<?=spasinum($no.'.',3).spasistr(" ",1).spasistr($data['nm_brg'],28)?>')
//               .text('<?=spasistr(" ",0).spasistr($qty_brg.strtolower($nm_sat),7)." ".spasinum(gantiti($hrg_jual),7)." ".spasinum(gantiti($discrp),7)." ".spasinum(gantiti($subtot),7)?>')
//               .print()
//             })
          
//           <?php
//         } // while 
//         unset($data);?>
//         printer.open().then(function () {
//           printer.align('left')
//           .text('--------------------------------')
//           .text('<?=spasistr(" ",3).spasistr("Total",12).spasistr(" ",3).'Rp. '.spasinum(gantiti($total),10)?>') 
//           .print()
//         })

//         if ('<?=$disctot?>' > 0){
//           printer.open().then(function () {
//             printer.align('left')
//             .text('<?=spasistr(" ",3).spasistr("Disc nota",12).spasistr(" ",3).'Rp. '.spasinum(gantiti($disctot),10)?>') 
//             .print()
//           })
//         }
        
//         if ('<?=$voucher?>' > 0){
//           printer.open().then(function () {
//             printer.align('left')
//             .text('<?=spasistr(" ",3).spasistr("Disc Voucher",12).spasistr(" ",3).'Rp. '.spasinum(gantiti(round($voucher,0)),10)?>') 
//             .print()
//           })
//         }
        
//         if ('<?=$ongkir?>' > 0){
//           printer.open().then(function () {
//             printer.align('left')
//             .text('<?=spasistr(" ",3).spasistr("Ongkir",12).spasistr(" ",3).'Rp. '.spasinum(gantiti($ongkir),10)?>') 
//             .print()
//           })
//         }    
        
//         if('<?=$kd_bayar?>' == 'TUNAI'){
//           printer.open().then(function () {
//             printer.align('left')
//             .text('<?=spasistr(" ",3).spasistr("Uang Tunai",12).spasistr(" ",3).'Rp. '.spasinum(gantiti($bayar),10)?>')
//             .text('<?=spasistr(" ",3).spasistr("Kembali",12).spasistr(" ",3).'Rp. '.spasinum(gantiti($susuk),10)?>')
//             .print()
//           })
//         } else {
//           printer.open().then(function () {
//             printer.align('left')
//               .text('<?=spasistr(" ",3).spasistr("Uang Tunai",12).spasistr(" ",3).'Rp. '.spasinum(gantiti($bayar),10)?>')
//               .text('<?=spasistr(" ",3).spasistr("Kekurangan",12).spasistr(" ",3).'Rp. '.spasinum(gantiti($saldohut),10)?>')
//               .text('<?=spasistr(" ",3).spasistr("Jatuh Tempo",12).spasistr(" ",3).'Rp. '.spasinum(gantitgl($tgl_jt),10)?>')
//             .print()
//           })
          
//         }  
//         printer.open().then(function () {
//             printer.align('center')
//             .raw('\n')
//             .text('TERIMA KASIH ATAS KUNJUNGAN ANDA')
//             .feed(2)
//             .cut(false,1)
//             .print()
//             .flush()
//         })
//       </script> <?php
//     }
//   } // mysqli_num_rows 
  
//   }
}

if ($pil_cetak =="CETAK-SM"){
  $sqlcari=mysqli_query($conbayar,"SELECT * from toko where kd_toko='$kd_toko'");
  $datacari=mysqli_fetch_assoc($sqlcari);
  $nm_toko=$datacari['nm_toko'];
  
  $al_toko=$datacari['al_toko'];
  unset($sqlcari,$datacari); 

  $sqlcari=mysqli_query($conbayar,"SELECT * from pelanggan where kd_pel='$kd_pel'");
  $datacari=mysqli_fetch_assoc($sqlcari);
  $nm_pel=$datacari['nm_pel'];
  $alamat=$datacari['al_pel'];
  unset($sqlcari,$datacari);
  $jam=date('d-m-Y / H:m:s');
  ?>
   <a id="gocet" style="display:none" href="f_jual_cetak_sm.php?nof=<?=$no_fakjual.';'.$tgl_jual.';'.$kd_toko.';'.$kd_pel.';'.$nm_toko.';'.$al_toko.';'.$nm_pel.';'.$alamat.';'.$kd_bayar.';'.$bayar.';'.$susuk.';'.$disctot.';'.$ongkir.';'.$saldohut.';'.$tgl_jt.';'.$jam.';'.$_SESSION['nm_user'].';'.$voucher?>" target="_blank" >webpage print</a>

  
  <script>
    document.getElementById("gocet").click();
  </script>
  <?php 
}  
//mysqli_close($conbayar);

if ($d)
{
  if ($update==1) {
   ?><script type="text/javascript">popnew_ok('Berhasil disimpan silahkan update piutang');</script><?php
  } else {
   $msg_cetak = (!empty($pil_cetak)) ? 'Berhasil disimpan....' . $pil_cetak : 'Berhasil disimpan';
   ?><script type="text/javascript">popnew_ok('<?= $msg_cetak; ?>');</script><?php
  }
} else { 
  ?><script type="text/javascript">popnew_ok('Data gagal disimpan');</script><?php
}    
?><script> kosongkan2();aktif();</script><?php
  $html = ob_get_contents(); 
  ob_end_clean();
  
  // Set header JSON before any output
  if (!headers_sent()) {
    header('Content-Type: application/json; charset=UTF-8');
  }
  
  echo json_encode(array('hasil'=>$html), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;