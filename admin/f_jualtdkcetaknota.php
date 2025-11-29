<?php 
  $keyword = $_POST['keyword']; // Ambil data keyword yang dikirim dengan AJAX  
  ob_start();

  
	$open = chr(27).chr(112).chr(48).chr(25).chr(250);
	$Text  = $open;

	$printer = printer_open("GP-80220(Cut) Series"); //open printer
	printer_set_option($printer,PRINTER_MODE,"RAW");
	printer_write($printer, $Text);    
	printer_close($printer);

	// if ('$d') {
 //      header("location:f_jual.php?pesan=simpan");
 //    }else{header("location:f_jual.php?pesan=gagal");}
?>

<?php
  $html = ob_get_contents(); // Masukan isi dari view.php ke dalam variabel $html
  ob_end_clean();
  // Buat array dengan index hasil dan value nya $html
  // Lalu konversi menjadi JSON
  echo json_encode(array('hasil'=>$html));
?>