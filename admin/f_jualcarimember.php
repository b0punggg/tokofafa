<?php
	$keyword = $_POST['keyword']; // Ambil data keyword yang dikirim dengan AJAX	
	ob_start();
?>
<style>
  th {
  position: sticky;
  top: -1px; 
  color:#fff;
  background-color:#6271c8;
  box-shadow: 0 2px 2px -1px black;
  }
  table, td {
    border: 1px solid lightgrey;
    padding: 1px;
  }
  th {
    border: 1px solid grey;
    padding: 3px;
  }
  table {
    border-spacing: 2px;
  }
</style>

<div class="table-responsive" style="overflow-y:auto;overflow-x: auto;border-style: ridge;">
	  <table id="tabmember" class="table-bordered table-hover" style="font-size:10pt;background-color: white;width: 100%;border-collapse: collapse;white-space: nowrap;font-size:9pt">
	    <tr align="middle" class="yz-theme-l4" style="background-color: white;position:sticky;top:1px">
	      <th style="width: 2%">NO</th>
	      <th>NAMA</th>
	      <th style="width: 30%">ALAMAT</th>
	    </tr>
	    <?php
	    include "config.php";
	    session_start(); 
     	    
	    $con1=opendtcek();
	    
	    // Ambil semua data member tanpa pagination (sama seperti pelanggan)
	    $sql1 = mysqli_query($con1, "SELECT * from member ORDER BY nm_member ASC");
	    
	    $no=0;
	    while($databrg = mysqli_fetch_array($sql1)){ // Ambil semua data dari hasil eksekusi $sql
	      $no++;
	      
	    ?>
	      <tr>
	        <td style="text-align: right"><?php echo $no?>&nbsp;</td>
	        <td>
	          <input class="w3-input" type="text" readonly value="<?=$databrg['nm_member']; ?>"
	          style="border: none;background-color: transparent;cursor: pointer"
	          onkeydown="if(event.keyCode==13){this.click()}" 
	          onclick="document.getElementById('<?='pilmember'.$no?>').click();">
	        </td>

	        <td align="left" class="button" style="cursor:pointer;">
	          <input id="<?='pilmember'.$no?>" class="w3-input" type="text" readonly="" value="<?=$databrg['al_member']; ?>" 
	          style="border: none;background-color: transparent;cursor: pointer"
	          onkeydown="if(event.keyCode==13){this.click()}" 
             onclick="
               document.getElementById('kd_member_byr').value='<?=mysqli_escape_string($con1,$databrg['kd_member']) ?>';
               document.getElementById('nm_memberbayar').value='<?=mysqli_escape_string($con1,$databrg['nm_member']) ?>';
               document.getElementById('poin_member').value='<?=isset($databrg['poin']) ? $databrg['poin'] : 0 ?>';
               document.getElementById('viewidmemberbayar').style.display='none';
               if(document.getElementById('poin_redeem')){
                 document.getElementById('poin_redeem').value='0';
               }
               if(document.getElementById('poin_redeem_hidden')){
                 document.getElementById('poin_redeem_hidden').value='0';
               }
               setTimeout(function(){ 
                 try { 
                   // Panggil hitdisc() terlebih dahulu untuk update diskon member dan total
                   if(typeof hitdisc === 'function') {
                     hitdisc();
                   } else if(typeof window.hitungdiscmember === 'function') {
                     window.hitungdiscmember();
                     if(typeof window.hitungpoin === 'function') {
                       window.hitungpoin(true);
                     }
                   } else if(typeof window.hitungpoin === 'function') { 
                     window.hitungpoin(false); 
                   } else if(typeof hitungpoin === 'function') { 
                     hitungpoin(false); 
                   }
                 } catch(e) { 
                   console.error('Error calling functions:', e); 
                 } 
               }, 500);
             ">
	        </td>
	      </tr>
	    <?php
	    }
	    ?>
	  </table>
</div>

<?php
    mysqli_close($con1);
	$html = ob_get_contents(); // Masukan isi dari view.php ke dalam variabel $html
	ob_end_clean();
	// Buat array dengan index hasil dan value nya $html
	// Lalu konversi menjadi JSON
	echo json_encode(array('hasil'=>$html));
?>

