<?php
	$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
	ob_start();
?>
<style>
  th {
  position: sticky;
  top: 0px; 
  /*color:#fff;
  background-color:#6271c8;*/
  box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
  }
  table, td {
    border: 1px solid grey;
    padding: 1px;
  }
  th {
    border: 1px solid lightgrey;
    padding: 3px;
  }
  table {
    border-spacing: 2px;
  }
</style>
<div class="table-responsive" style="overflow-y:auto;overflow-x: auto;border-style: ridge;border-color: white;height: 300px">
	  <table id="table" class=" arrow-nav table-hover" style="font-size:9pt;position: sticky;width: 100%">
	    <tr align="middle" class="yz-theme-l3">
	      <th width="5%">No.</th>
	      <th width="12%">ID CREW</th>
	      <th width="22%">NAMA CREW</th>
        <th width="15%">NAMA TOKO</th>
	      <th width="15%">ALAMAT</th>
	      <th width="12%">NO. TELP/HP</th>
	      <th width="8%">STATUS</th>
	      <th colspan="2" width="10%">OPSI</th>
	    </tr>
	    <?php
	    include "config.php";
	    if(!session_id()) session_start();
	    	
        $connect=opendtcek();
        $kd_toko = '';
        $nm_toko_sesi = '';
        if($connect){
          $kd_toko = isset($_SESSION['id_toko']) ? mysqli_real_escape_string($connect, $_SESSION['id_toko']) : '';
          $nm_toko_sesi = isset($_SESSION['nm_toko']) ? mysqli_real_escape_string($connect, $_SESSION['nm_toko']) : '';
        }
        $page = (isset($_POST['page'])) ? max(1, intval($_POST['page'])) : 1;

	    $limit = 10; // Jumlah data per halamannya

	    $limit_start = ($page - 1) * $limit;
      $sql = false;
      $sql2 = false;
      $get_jumlah = array('jumlah' => 0);

      if(!$connect){
        echo '<tr><td colspan="9" align="center">Koneksi database gagal</td></tr>';
      } else {
        include_once 'crew_helper.php';
        ensureCrewTable($connect);
        $kolom = array();
        $cek_kolom = mysqli_query($connect, "SHOW COLUMNS FROM crew");
        if($cek_kolom){
          while($row_kolom = mysqli_fetch_assoc($cek_kolom)){
            $kolom[$row_kolom['Field']] = true;
          }
          mysqli_free_result($cek_kolom);
        }

        $filter_where = array();
        if($kd_toko === ''){
          // Safety: tanpa otoritas toko, jangan tampilkan data crew.
          $filter_where[] = "1=0";
        } else if(isset($kolom['kd_toko'])){
          // Prioritas utama filter berdasarkan kd_toko akun login.
          // Tambahan fallback aman untuk data lama yang kd_toko masih kosong:
          // hanya ditampilkan jika nm_toko sama dengan toko sesi.
          if(isset($kolom['nm_toko']) && $nm_toko_sesi !== ''){
            $filter_where[] = "(kd_toko='$kd_toko' OR ((kd_toko='' OR kd_toko IS NULL) AND UPPER(TRIM(nm_toko))=UPPER(TRIM('$nm_toko_sesi'))))";
          } else {
            $filter_where[] = "kd_toko='$kd_toko'";
          }
        } else if(isset($kolom['id_toko'])){
          // Fallback untuk schema lama yang memakai id_toko.
          $filter_where[] = "id_toko='$kd_toko'";
        } else {
          // Jika tidak ada kolom otoritas toko, jangan tampilkan data lintas toko.
          $filter_where[] = "1=0";
        }
        if(isset($_POST['search']) && $_POST['search'] == true){
          $params = mysqli_real_escape_string($connect, $keyword);
          if($params !== ''){
            $filter_where[] = "(nm_crew LIKE '%$params%' OR kd_crew LIKE '%$params%')";
          }
        }

        $where_sql = '';
        if(count($filter_where) > 0){
          $where_sql = " WHERE ".implode(" AND ", $filter_where);
        }

        $sort = isset($_POST['sort']) ? $_POST['sort'] : 'abjad';
        $order_sql = 'nm_crew ASC';
        if ($sort === 'id_asc') {
          $order_sql = 'no_urut ASC';
        } elseif ($sort === 'id_desc') {
          $order_sql = 'no_urut DESC';
        }

        $sql = mysqli_query($connect, "SELECT * FROM crew $where_sql ORDER BY $order_sql LIMIT $limit_start, $limit");
        $sql2 = mysqli_query($connect, "SELECT COUNT(*) AS jumlah FROM crew $where_sql");
        if($sql2){
          $get_jumlah = mysqli_fetch_array($sql2);
        }

      }
	    $no=$limit_start;
	    while($sql && ($data = mysqli_fetch_array($sql))){ // Ambil semua data dari hasil eksekusi $sql
	      //$no++;
	      $id=mysqli_escape_string($connect,$data['nm_crew']);
	      if($id<>'-NONE-'){
	        $no++;		
	    ?>
	      <tr>
	        <td align="right"><?php echo $no.'.' ?></td>
	        <td align="left"><input class="w3-input" type="text" value="<?php echo $data['kd_crew']; ?>" readonly style="border: none;background-color: transparent;" ></td>
	        <td align="left"><input class="w3-input" type="text" value="<?php echo $data['nm_crew']; ?>" readonly style="border: none;background-color: transparent;" ></td>
          <td align="left"><input class="w3-input" type="text"
     value="<?php echo htmlspecialchars(isset($data['nm_toko']) ? $data['nm_toko'] : '-'); ?>" readonly
     style="border: none;background-color: transparent;" ></td>
	        <td align="left"><input class="w3-input" type="text" value="<?php echo htmlspecialchars(isset($data['al_crew']) ? $data['al_crew'] : ''); ?>" readonly style="border: none;background-color: transparent;" ></td>
	        <td align="left"><input class="w3-input" type="text" value="<?php echo htmlspecialchars(isset($data['no_telp']) ? $data['no_telp'] : ''); ?>" readonly style="border: none;background-color: transparent;" ></td>
	        <td align="center"><?php echo (isset($data['aktif']) && (int)$data['aktif'] === 1) ? 'Aktif' : 'Nonaktif'; ?></td>
	        <td>
	          	<button onclick="document.getElementById('kd_crew').value='<?=mysqli_escape_string($connect,$data['kd_crew']) ?>';
	          	   document.getElementById('nm_crew').value='<?=mysqli_escape_string($connect,$data['nm_crew']) ?>';
	          	   document.getElementById('al_crew').value='<?=mysqli_escape_string($connect,isset($data['al_crew']) ? $data['al_crew'] : '') ?>';
	          	   document.getElementById('no_telp').value='<?=mysqli_escape_string($connect,isset($data['no_telp']) ? $data['no_telp'] : '') ?>';
	          	   document.getElementById('no_urut').value='<?=mysqli_escape_string($connect,$data['no_urut']) ?>';
               document.getElementById('aktif').checked=<?= (isset($data['aktif']) && (int)$data['aktif']===1) ? 'true' : 'false' ?>;
	        	   document.getElementById('keyktcrew').value='';
	        	   document.getElementById('btn-ktcrew').click()" class="btn-primary fa fa-edit" style="cursor: pointer; border-style:none;font-size: 12pt" title="Edit Data">
	            </button>	    
	        </td>
	        <td>
	           <?php $param=mysqli_escape_string($connect,$data['no_urut']); ?>
	           <button onclick="if(confirm('Apakah anda yakin ingin menghapus data ini ??')){ location.href='m_crewhapus_act.php?param=<?php echo $param ?>'}" class="btn-danger fa fa-trash" style="cursor: pointer;font-size: 12pt" title="Hapus Data"></button>
	           
	        </td>    
	      </tr>
	    <?php
	      }
	    }
      if($connect && !$sql){
        ?>
        <tr>
          <td colspan="9" align="center">Query crew gagal: <?php echo htmlspecialchars(mysqli_error($connect)); ?></td>
        </tr>
        <?php
      }
	    ?>
	  </table>
	</div>
	<?php 
	  if ($get_jumlah['jumlah']>=1){
	  	?>
	  	<script>document.getElementById("ket_rec").innerHTML=" Total Data "+<?php echo $get_jumlah['jumlah'] ?>+" Record" </script>	
	  	<?php
	  }else {
	  	?>
	  	<script>document.getElementById("ket_rec").innerHTML=" Belum Ada Data" </script>	
	  	<?php
	  }
	?>
	<div class="w3-border">
		<nav  aria-label="Page navigation example" style="margin-top:5px;font-size: 9pt">
		  <ul class="pagination justify-content-start">
		    <!-- LINK FIRST AND PREV -->
		    <?php
		    if($page == 1){ // Jika page adalah page ke 1, maka disable link PREV
		    ?>
		      <li class="page-item disabled "><a class="page-link  yz-theme-d1" href="javascript:void(0)" style="cursor: no-drop">First</a></li>
		      <li class="page-item disabled "><a class="page-link  yz-theme-l1" href="javascript:void(0)" style="cursor: no-drop">&laquo;</a></li>
		    <?php
		    }else{ // Jika page bukan page ke 1
		      $link_prev = ($page > 1)? $page - 1 : 1;
		    ?>
		      <li><a class="page-link yz-theme-d1" style="cursor: pointer" href="javascript:void(0);" onclick="caricrew(1, false)">First</a></li>
		      <li><a class="page-link yz-theme-l1" style="cursor: pointer" href="javascript:void(0);" onclick="caricrew(<?php echo $link_prev; ?>, false)">&laquo;</a></li>
		    <?php
		    }
		    ?>
		    
		    <!-- LINK NUMBER -->
		    <?php
		    $jumlah_page = ceil($get_jumlah['jumlah'] / $limit); // Hitung jumlah halamannya
		    $jumlah_number = 1; // Tentukan jumlah link number sebelum dan sesudah page yang aktif
		    $start_number = ($page > $jumlah_number)? $page - $jumlah_number : 1; // Untuk awal link number
		    $end_number = ($page < ($jumlah_page - $jumlah_number))? $page + $jumlah_number : $jumlah_page; // Untuk akhir link number
		    
		    for($i = $start_number; $i <= $end_number; $i++){
		      $link_active = ($page == $i)? ' class="active"' : '';
		    ?>
		      <li class="page-item " <?php echo $link_active; ?>><a class="page-link  yz-theme-l3" href="javascript:void(0);" style="cursor: pointer" onclick="caricrew(<?php echo $i; ?>, false)"><?php echo $i; ?></a></li>
		    <?php
		    }
		    ?>
		    
		    <!-- LINK NEXT AND LAST -->
		    <?php
		    if($page == $jumlah_page || $get_jumlah['jumlah']==0){ // Jika page terakhir
		    ?>
		      <li class="page-item disabled " ><a class="page-link  yz-theme-l1" href="javascript:void(0)" style="cursor: no-drop">&raquo;</a></li>
		      <li class="page-item disabled "><a class="page-link yz-theme-d1" href="javascript:void(0)" style="cursor: no-drop">Last</a></li>
		    <?php
		    }else{ // Jika Bukan page terakhir
		      $link_next = ($page < $jumlah_page)? $page + 1 : $jumlah_page;
		    ?>
		      <li class="page-item"><a class="page-link yz-theme-l1" href="javascript:void(0)" onclick="caricrew(<?php echo $link_next; ?>, false)" style="cursor: pointer">&raquo;</a></li>
		      <li class="page-item "><a class="page-link yz-theme-d1" href="javascript:void(0)" onclick="caricrew(<?php echo $jumlah_page; ?>, false)" style="cursor: pointer">Last</a></li>
		    <?php
		    }
		    ?>
		  </ul>
		</nav>
	</div>

<!--  -->
<script>
$('table.arrow-nav').keydown(function(e){
    var $table = $(this);
    var $active = $('input:focus,select:focus',$table);
    var $next = null;
    var focusableQuery = 'input:visible,select:visible,textarea:visible';
    var position = parseInt( $active.closest('td').index()) + 1;
    console.log('position :',position);
    switch(e.keyCode){
        case 37: // <Left>
            $next = $active.parent('td').prev().find(focusableQuery);   
            break;
        case 38: // <Up>                    
            $next = $active
                .closest('tr')
                .prev()                
                .find('td:nth-child(' + position + ')')
                .find(focusableQuery)
            ;
            
            break;
        case 39: // <Right>
            $next = $active.closest('td').next().find(focusableQuery);            
            break;
        case 40: // <Down>
            $next = $active
                .closest('tr')
                .next()                
                .find('td:nth-child(' + position + ')')
                .find(focusableQuery)
            ;
            break;
    }       
    if($next && $next.length)
    {        
        $next.focus();
    }
});
</script>
<!--  -->		

<?php
    if($connect){
      mysqli_close($connect);
    }
	$html = ob_get_contents(); // Masukan isi dari view.php ke dalam variabel $html
	ob_end_clean();
	echo $html;
?>
