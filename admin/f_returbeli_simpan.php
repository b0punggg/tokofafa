<?php 
ob_start();
include 'config.php';
session_start();

$consim=opendtcek();
$kd_toko=$_SESSION['id_toko'];
$no_tran=mysqli_real_escape_string($consim,$_POST['keyword1']);
$tgl_tran=mysqli_real_escape_string($consim,$_POST['keyword2']);
$kembali=mysqli_real_escape_string($consim,$_POST['keyword3']);
// echo '$no_tran='.$no_tran.'<br>';
// echo '$tgl_tran='.$tgl_tran.'<br>';
// echo '$kembali='.$kembali.'<br>';
$hrg_beli=0;$subtot=0;$totpot=0;$tottax=0;$totretur=0;$totawal=0;$jml_brgakhir=0;$jml_brg_klr=0;$no=0;$d=false;
//apakah sudah disave atau belum
$cekmas=mysqli_query($consim,"SELECT * FROM retur_beli_mas WHERE kd_toko='$kd_toko' AND no_retur='$no_tran' AND tgl_retur='$tgl_tran'");
if (mysqli_num_rows($cekmas)>=1){
   $datmas=mysqli_fetch_assoc($cekmas);
   $no_urutmas=$datmas['no_urut'];
   $cek=mysqli_query($consim,"SELECT * FROM retur_beli WHERE kd_toko='$kd_toko' AND no_retur='$no_tran' AND tgl_retur='$tgl_tran'");
	if (mysqli_num_rows($cek)>=1){
	  while ($datcek=mysqli_fetch_assoc($cek)){
	  	//konstanta total
	    $disc=$datcek['hrg_beli']-($datcek['hrg_beli']*($datcek['disc']/100));
		$tax=($datcek['hrg_beli']*($datcek['tax']/100));
		$subtot=$subtot+round(($disc+$tax)*$datcek['qty_brg'],2);
		$totpot=$totpot+($datcek['hrg_beli']*($datcek['disc']/100));
		$tottax=$tottax+$tax;$totretur=$totretur+$subtot;
		$totawal=$totawal+$datcek['hrg_beli'];
		$no++;

	    //proses cek pada beli_brg dan mas_brg
	    $kd_brg=$datcek['kd_brg'];
	    $no_urut=$datcek['no_urut'];
	    $no_item=$datcek['no_item'];
	    $kd_sup=$datcek['kd_sup'];
	    $jml_retur=$datcek['qty_brg']*konjumbrg($datcek['kd_sat'],$kd_brg);
	    $stok_akhir=caristokbeli($no_item,$kd_brg)-$jml_retur;
	    $x=explode(';',caristokmas($kd_brg));
	    $jml_brgakhir=$x[0]-$jml_retur;
	    $jml_brg_klr=$x[2]+$jml_retur;

	    //proses simpan pengembalian
	    $d=mysqli_query($consim,"UPDATE retur_beli SET kembali='$kembali' where no_urut='$no_urut'"); 
	    //$d=mysqli_query($consim,"UPDATE beli_brg SET jml_stok='$stok_akhir' where no_urut='$no_item'");
	    //$d=mysqli_query($consim,"UPDATE mas_brg SET jml_brg='$jml_brgakhr',brg_klr='$jml_brg_klr' where kd_brg='$kd_brg'");
	  }	
	  //proses simpan pada retur_beli_mas
	  $d=mysqli_query($consim,"UPDATE retur_beli_mas SET tot_qty='$no',tot_hrg_beli='$totawal',tot_potongan='$totpot',tot_tax='$tottax',tot_retur='$subtot',tot_kembali='$kembali' WHERE no_urut='$no_urutmas'");
	}     
	unset($cek,$datcek);
} else {
	$cek=mysqli_query($consim,"SELECT * FROM retur_beli WHERE kd_toko='$kd_toko' AND no_retur='$no_tran' AND tgl_retur='$tgl_tran'");
	if (mysqli_num_rows($cek)>=1){
	  while ($datcek=mysqli_fetch_assoc($cek)){
	  	//konstanta total
	    $disc=$datcek['hrg_beli']-($datcek['hrg_beli']*($datcek['disc']/100));
		$tax=($datcek['hrg_beli']*($datcek['tax']/100));
		$subtot=$subtot+round(($disc+$tax)*$datcek['qty_brg'],2);
		$totpot=$totpot+($datcek['hrg_beli']*($datcek['disc']/100));
		$tottax=$tottax+$tax;$totretur=$totretur+$subtot;
		$totawal=$totawal+$datcek['hrg_beli'];
		$no++;	

	    //proses cek pada beli_brg dan mas_brg
	    $kd_brg=$datcek['kd_brg'];
	    $no_urut=$datcek['no_urut'];
	    $no_item=$datcek['no_item'];
	    $kd_sup=$datcek['kd_sup'];
	    $jml_retur=$datcek['qty_brg']*konjumbrg($datcek['kd_sat'],$kd_brg);
	    $stok_akhir=caristokbeli($no_item,$kd_brg)-$jml_retur;
	    //echo '$stok_akhir='.$stok_akhir.'<br>';
	    $x=explode(';',caristokmas($kd_brg));
	    $jml_brgakhir=$x[0]-$jml_retur;
	    $jml_brg_klr=$x[2]+$jml_retur;

	    //proses simpan pengembalian
	    $d=mysqli_query($consim,"UPDATE retur_beli SET kembali='$kembali' where no_urut='$no_urut'"); 
	    $d=mysqli_query($consim,"UPDATE beli_brg SET stok_jual='$stok_akhir' where no_urut='$no_item'");
	    $d=mysqli_query($consim,"UPDATE mas_brg SET jml_brg='$jml_brgakhir',brg_klr='$jml_brg_klr' where kd_brg='$kd_brg'");
	  }	
	  //proses simpan pada retur_beli_mas
	  $d=mysqli_query($consim,"INSERT INTO retur_beli_mas VALUES('','$tgl_tran','$no_tran','$kd_sup','$no','$totawal','$totpot','$tottax','$subtot','$kembali','$kd_toko')");
	}
}
unset($cekmas,$datmas);

if ($d) {
 ?> <script>popnew_ok("Simpan berhasil");kosongkan();carinoretur(1,true);</script> <?php   	
}else {?> <script>popnew_error("Simpan gagal");kosongkan();carinoretur(1,true);</script> <?php }
?>
<?php
  mysqli_close($consim);
	$html = ob_get_contents(); // Masukan isi dari view.php ke dalam variabel $html
	ob_end_clean();
	// Buat array dengan index hasil dan value nya $html
	// Lalu konversi menjadi JSON
	echo json_encode(array('hasil'=>$html));
?>
