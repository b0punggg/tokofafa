<?php
	// Tingkatkan waktu eksekusi untuk query yang kompleks
	set_time_limit(60); // 60 detik
	ini_set('max_execution_time', 60);
	
	$keyword = $_POST['keyword']; // Ambil data keyword yang dikirim dengan AJAX	
	ob_start();
	session_start(); 
     	
	include "config.php";
    $connect=opendtcek();
?>

<div class="table-responsive" style="overflow-x:auto;overflow-y:auto; border-style: ridge;">
	<table id="jnmbrg" class="table-hover" style="font-size:9pt;width:100%;border-collapse: collapse;white-space: nowrap;">
	    <tr align="middle" class="yz-theme-l1">
	      <!-- <th>KD. BRG.</th> -->
	      <th >NAMA BARANG</th>
		  <th style ="width:5%">HRG.JUAL</th>
	      <th style ="width:5%">STOK</th>
	      <th style ="width:3%">SAT</th>
	      <th style ="width:2%">OPSI</th>
	    </tr>
	    <?php
	    $page = (isset($_POST['page']))? $_POST['page'] : 1;

	    $limit = 10; // Jumlah data per halamannya

	    $limit_start = ($page - 1) * $limit;
	    // echo '$limit_start='.$limit_start;
        $id_toko=$_SESSION['id_toko'];
	    if(isset($_POST['search']) && $_POST['search'] == true){ // Jika ada data search yg 
   		    
	    	$params = mysqli_real_escape_string($connect, $keyword);
	    	$param='%'.trim($params).'%';  	
	    	
          if ($params=="") {	 
          	// Coba query dengan LEFT JOIN dulu untuk memastikan semua barang ditampilkan
          	$query1 = "SELECT mas_brg.kd_brg, 
          		IFNULL(SUM(beli_brg.stok_jual), 0) AS jumstok,
          		mas_brg.nm_brg,mas_brg.jum_kem1,mas_brg.jum_kem2,mas_brg.jum_kem3,
          		mas_brg.kd_kem1,mas_brg.kd_kem2,mas_brg.kd_kem3,
          		mas_brg.hrg_jum1,mas_brg.hrg_jum2,mas_brg.hrg_jum3,
          		IFNULL(beli_brg.id_bag, '') AS id_bag
          	  	FROM mas_brg 
          	  	LEFT JOIN beli_brg ON mas_brg.kd_brg=beli_brg.kd_brg AND mas_brg.kd_toko=beli_brg.kd_toko AND beli_brg.stok_jual>0
          	  	WHERE mas_brg.kd_toko='$id_toko'
				GROUP BY mas_brg.kd_brg
				HAVING jumstok > 0
          	    ORDER BY mas_brg.kd_brg ASC LIMIT $limit_start, $limit";
          	$sql1 = mysqli_query($connect, $query1);
          	
          	if (!$sql1) {
          		// Jika query gagal, coba query alternatif dengan filter stok
          		$query1_alt = "SELECT mas_brg.kd_brg, 
          			IFNULL(SUM(beli_brg.stok_jual), 0) AS jumstok,
          			mas_brg.nm_brg,mas_brg.jum_kem1,mas_brg.jum_kem2,mas_brg.jum_kem3,
          			mas_brg.kd_kem1,mas_brg.kd_kem2,mas_brg.kd_kem3,
          			mas_brg.hrg_jum1,mas_brg.hrg_jum2,mas_brg.hrg_jum3
          			FROM mas_brg 
          			LEFT JOIN beli_brg ON mas_brg.kd_brg=beli_brg.kd_brg AND mas_brg.kd_toko=beli_brg.kd_toko AND beli_brg.stok_jual>0
          			WHERE mas_brg.kd_toko='$id_toko'
          			GROUP BY mas_brg.kd_brg
          			HAVING jumstok > 0
          			ORDER BY mas_brg.kd_brg ASC LIMIT $limit_start, $limit";
          		$sql1 = mysqli_query($connect, $query1_alt);
          	}
          	
          	// Optimasi query COUNT dengan query yang lebih sederhana
          	$query2 = "SELECT COUNT(DISTINCT mas_brg.kd_brg) AS jumlah 
          		FROM mas_brg 
          		INNER JOIN beli_brg ON mas_brg.kd_brg=beli_brg.kd_brg AND mas_brg.kd_toko=beli_brg.kd_toko
          		WHERE mas_brg.kd_toko='$id_toko' AND beli_brg.stok_jual > 0";
          	$sql2 = mysqli_query($connect, $query2);
          }
          else {
	        // Query dengan pencarian multi-kriteria: nama, kode barang, dan barcode
	        $query1 = "SELECT mas_brg.kd_brg, 
	        	IFNULL(SUM(beli_brg.stok_jual), 0) AS jumstok,
	        	mas_brg.nm_brg,mas_brg.jum_kem1,mas_brg.jum_kem2, mas_brg.jum_kem3,
	        	mas_brg.kd_kem1,mas_brg.kd_kem2,mas_brg.kd_kem3,
	        	mas_brg.hrg_jum1,mas_brg.hrg_jum2,mas_brg.hrg_jum3,
	        	IFNULL(beli_brg.id_bag, '') AS id_bag
          	  	FROM mas_brg 
          	  	LEFT JOIN beli_brg ON mas_brg.kd_brg=beli_brg.kd_brg AND mas_brg.kd_toko=beli_brg.kd_toko AND beli_brg.stok_jual>0
          	  	WHERE mas_brg.kd_toko='$id_toko'
          	  		AND (
          	  			mas_brg.nm_brg LIKE '$param' 
          	  			OR mas_brg.kd_brg LIKE '$param'
          	  			OR (mas_brg.kd_bar IS NOT NULL AND mas_brg.kd_bar != '' AND mas_brg.kd_bar LIKE '$param')
          	  		)
          	  	GROUP BY mas_brg.kd_brg
          	  	HAVING jumstok > 0
          	    ORDER BY mas_brg.nm_brg ASC LIMIT $limit_start, $limit";
          	$sql1 = mysqli_query($connect, $query1);
          	
          	if (!$sql1) {
          		// Jika query gagal, coba query alternatif dengan filter stok
          		$query1_alt = "SELECT mas_brg.kd_brg, 
          			IFNULL(SUM(beli_brg.stok_jual), 0) AS jumstok,
          			mas_brg.nm_brg,mas_brg.jum_kem1,mas_brg.jum_kem2, mas_brg.jum_kem3,
          			mas_brg.kd_kem1,mas_brg.kd_kem2,mas_brg.kd_kem3,
          			mas_brg.hrg_jum1,mas_brg.hrg_jum2,mas_brg.hrg_jum3
          			FROM mas_brg 
          			LEFT JOIN beli_brg ON mas_brg.kd_brg=beli_brg.kd_brg AND mas_brg.kd_toko=beli_brg.kd_toko AND beli_brg.stok_jual>0
          			WHERE mas_brg.kd_toko='$id_toko'
          				AND (
          					mas_brg.nm_brg LIKE '$param' 
          					OR mas_brg.kd_brg LIKE '$param'
          					OR (mas_brg.kd_bar IS NOT NULL AND mas_brg.kd_bar != '' AND mas_brg.kd_bar LIKE '$param')
          				)
          			GROUP BY mas_brg.kd_brg
          			HAVING jumstok > 0
          			ORDER BY mas_brg.nm_brg ASC LIMIT $limit_start, $limit";
          		$sql1 = mysqli_query($connect, $query1_alt);
          	}
          	
          	// Optimasi query COUNT dengan query yang lebih sederhana
          	$query2 = "SELECT COUNT(DISTINCT mas_brg.kd_brg) AS jumlah 
          		FROM mas_brg 
          		INNER JOIN beli_brg ON mas_brg.kd_brg=beli_brg.kd_brg AND mas_brg.kd_toko=beli_brg.kd_toko
          		WHERE mas_brg.kd_toko='$id_toko' AND beli_brg.stok_jual > 0
          			AND (
          				mas_brg.nm_brg LIKE '$param' 
          				OR mas_brg.kd_brg LIKE '$param'
          				OR (mas_brg.kd_bar IS NOT NULL AND mas_brg.kd_bar != '' AND mas_brg.kd_bar LIKE '$param')
          			)";
          	$sql2 = mysqli_query($connect, $query2);
          }
	      
	      // Cek apakah query berhasil sebelum fetch
	      if ($sql2 !== false) {
	      	$get_jumlah = mysqli_fetch_array($sql2);
	      } else {
	      	$get_jumlah = array('jumlah' => 0);
	      }

	    }else{ // Jika user belum mengklik tombol search (PROSES TANPA AJAX)
			$query1 = "SELECT mas_brg.kd_brg, 
				IFNULL(SUM(beli_brg.stok_jual), 0) AS jumstok,
				mas_brg.nm_brg,mas_brg.jum_kem1,mas_brg.jum_kem2,mas_brg.jum_kem3,
				mas_brg.kd_kem1,mas_brg.kd_kem2,mas_brg.kd_kem3,
				mas_brg.hrg_jum1,mas_brg.hrg_jum2,mas_brg.hrg_jum3,
				IFNULL(beli_brg.id_bag, '') AS id_bag
			FROM mas_brg 
			LEFT JOIN beli_brg ON mas_brg.kd_brg=beli_brg.kd_brg AND mas_brg.kd_toko=beli_brg.kd_toko AND beli_brg.stok_jual>0
			WHERE mas_brg.kd_toko='$id_toko'
			GROUP BY mas_brg.kd_brg
			HAVING jumstok > 0
		    ORDER BY mas_brg.kd_brg ASC LIMIT $limit_start, $limit";
			$sql1 = mysqli_query($connect, $query1);
			
			if (!$sql1) {
				// Jika query gagal, coba query alternatif dengan filter stok
				$query1_alt = "SELECT mas_brg.kd_brg, 
					IFNULL(SUM(beli_brg.stok_jual), 0) AS jumstok,
					mas_brg.nm_brg,mas_brg.jum_kem1,mas_brg.jum_kem2,mas_brg.jum_kem3,
					mas_brg.kd_kem1,mas_brg.kd_kem2,mas_brg.kd_kem3,
					mas_brg.hrg_jum1,mas_brg.hrg_jum2,mas_brg.hrg_jum3
					FROM mas_brg 
					LEFT JOIN beli_brg ON mas_brg.kd_brg=beli_brg.kd_brg AND mas_brg.kd_toko=beli_brg.kd_toko AND beli_brg.stok_jual>0
					WHERE mas_brg.kd_toko='$id_toko'
					GROUP BY mas_brg.kd_brg
					HAVING jumstok > 0
					ORDER BY mas_brg.kd_brg ASC LIMIT $limit_start, $limit";
				$sql1 = mysqli_query($connect, $query1_alt);
			}
			
			// Optimasi query COUNT dengan query yang lebih sederhana
			$query2 = "SELECT COUNT(DISTINCT mas_brg.kd_brg) AS jumlah 
				FROM mas_brg 
				INNER JOIN beli_brg ON mas_brg.kd_brg=beli_brg.kd_brg AND mas_brg.kd_toko=beli_brg.kd_toko
				WHERE mas_brg.kd_toko='$id_toko' AND beli_brg.stok_jual > 0";
	  		$sql2 = mysqli_query($connect, $query2);
	  		
	  		// Cek apakah query berhasil sebelum fetch
	  		if ($sql2 !== false) {
	  			$get_jumlah = mysqli_fetch_array($sql2);
	  		} else {
	  			$get_jumlah = array('jumlah' => 0);
	  		}
	    }
	    $no=0;$stok=0;$hrg_jual=0;
	    
	    // Cek apakah query sql1 berhasil sebelum loop
	    if ($sql1 !== false && mysqli_num_rows($sql1) > 0) {
	    	while($databrg = mysqli_fetch_array($sql1)) { 
			$no++;
			// Gunakan stok yang sudah dihitung dari query utama (jumstok) untuk menghindari query tambahan
			$stok = isset($databrg['jumstok']) ? floatval($databrg['jumstok']) : 0;
			// Jika jumstok tidak ada, baru panggil caristok (fallback)
			if ($stok == 0) {
				$stok = caristok($databrg['kd_brg'],$connect);
			}
			
			// Handle jika jum_kem tidak ada atau null
			$jum_kem1 = isset($databrg['jum_kem1']) ? floatval($databrg['jum_kem1']) : 0;
			$jum_kem2 = isset($databrg['jum_kem2']) ? floatval($databrg['jum_kem2']) : 0;
			$jum_kem3 = isset($databrg['jum_kem3']) ? floatval($databrg['jum_kem3']) : 0;
			
			if($jum_kem3==0 && $jum_kem2==0){
				$kd_kem = isset($databrg['kd_kem1']) && !empty($databrg['kd_kem1']) ? mysqli_escape_string($connect, $databrg['kd_kem1']) : '';
				if (!empty($kd_kem)) {
					$satkecil=ceknmkem2($kd_kem, $connect);
				} else {
					$satkecil = 'PCS';
				}
				$hrg_jual=isset($databrg['hrg_jum1']) ? floatval($databrg['hrg_jum1']) : 0;
			}
			elseif($jum_kem3==0 && $jum_kem2>0){
				$kd_kem = isset($databrg['kd_kem2']) && !empty($databrg['kd_kem2']) ? mysqli_escape_string($connect, $databrg['kd_kem2']) : '';
				if (!empty($kd_kem)) {
					$satkecil=ceknmkem2($kd_kem, $connect);
				} else {
					$satkecil = 'PCS';
				}
				$hrg_jual=isset($databrg['hrg_jum2']) ? floatval($databrg['hrg_jum2']) : 0;
			}
			elseif($jum_kem3>0) {
				$kd_kem = isset($databrg['kd_kem3']) && !empty($databrg['kd_kem3']) ? mysqli_escape_string($connect, $databrg['kd_kem3']) : '';
				if (!empty($kd_kem)) {
					$satkecil=ceknmkem2($kd_kem, $connect);
				} else {
					$satkecil = 'PCS';
				}
				$hrg_jual=isset($databrg['hrg_jum3']) ? floatval($databrg['hrg_jum3']) : 0;
			} else {
				// Default jika tidak ada kemasan
				$satkecil = 'PCS';
				$hrg_jual = isset($databrg['hrg_jum1']) ? floatval($databrg['hrg_jum1']) : 0;
			} ?>
			<tr>  
				<td align="left">&nbsp;<input type="text" 
					onkeydown="if(event.keyCode==13){document.getElementById('<?='tmb'.$no?>').click();}" 
					onclick="document.getElementById('<?='tmb'.$no?>').click();" 
					value="<?php echo $databrg['nm_brg'];?>" 
					readonly class="hrf_res"
					style="border:none;background-color: transparent;cursor: pointer;width:300px" tabindex='7'>
				</td>
				<td align="right"><input  type="text" 
					onkeydown="if(event.keyCode==13){document.getElementById('<?='tmb'.$no?>').click();}" 
					onclick="document.getElementById('<?='tmb'.$no?>').click();" 
					value="<?php echo gantitides($hrg_jual);?>" 
					readonly class="hrf_res"
					style="border:none;background-color: transparent;cursor: pointer;text-align: right;width:100px">&nbsp;
				</td>
				<td align="right">
					<input type="text" 
					onkeydown="if(event.keyCode==13){document.getElementById('<?='tmb'.$no?>').click();}" 
					onclick="document.getElementById('<?='tmb'.$no?>').click();" 
					value="<?php echo $stok ?>" 
					readonly class="hrf_res"
					style="border:none;background-color: transparent;cursor: pointer;text-align: right;width:70px">
					&nbsp;
				</td>
				<td align="middle">
					<input  type="text" 
					onkeydown="if(event.keyCode==13){document.getElementById('<?='tmb'.$no?>').click();}" 
					onclick="document.getElementById('<?='tmb'.$no?>').click();" 
					value="<?php echo $satkecil; ?>" 
					readonly class="hrf_res"
					style="border:none;background-color: transparent;cursor: pointer;width:50px;text-align: center;">
				</td>			  
				<td class="w3-center">
					<button id="<?='tmb'.$no?>" type="button" class="btn btn-primary fa fa-search"
					onkeydown="if(event.keyCode==13){this.click();}" 
					onclick="document.getElementById('kd_brg').value='<?=$databrg['kd_brg'] ?>';
					    // Ambil discount promo untuk barang ini (tanpa harga jual dulu, hanya discount rupiah)
					    getdiscpromo('<?=$databrg['kd_brg'] ?>', 0);
					    carisatbrg();
						document.getElementById('viewnmbrg').style.display='none';
						document.getElementById('viewnmbrgsm').style.display='none';
						document.getElementById('nm_sat').focus()"
					readonly></button>
				</td>    
			</tr>
			<?php
			}
		} else {
			// Jika query gagal, tampilkan pesan error atau kosong
			?>
			<tr>
				<td colspan="5" align="center" style="padding: 20px;">
					<i class="fa fa-exclamation-triangle"></i> Tidak ada data atau terjadi kesalahan
				</td>
			</tr>
			<?php
		}
		
	    ?>
	</table>
</div>
	
	<div class="w3-border yz-theme-l5">
		<nav  aria-label="Page navigation example" style="margin-top:15px;font-size: 9pt">
		  <ul class="pagination justify-content-center">
		    <!-- LINK FIRST AND PREV -->
		    <?php
		    if($page == 1){ // Jika page adalah page ke 1, maka disable link PREV
		    ?>
		      <li class="page-item disabled "><a class="page-link  yz-theme-d1" href="javascript:void(0)" style="cursor: no-drop">First</a></li>
		      <li class="page-item disabled "><a class="page-link  yz-theme-l1" href="javascript:void(0)" style="cursor: no-drop;padding-left:15px;padding-right:15px"><i class="fa fa-chevron-left"></i></a></li>
		    <?php
		    }else{ // Jika page bukan page ke 1
		      $link_prev = ($page > 1)? $page - 1 : 1;
		    ?>
		      <li><a class="page-link yz-theme-d1" style="cursor: pointer" href="javascript:void(0);" onclick="carinmbrg(1, false)">First</a></li>
		      <li><a class="page-link yz-theme-l1" style="cursor: pointer;padding-left:15px;padding-right:15px" href="javascript:void(0);" onclick="carinmbrg(<?php echo $link_prev; ?>, false)"><i class="fa fa-chevron-left"></i></a></li>
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
		      <li class="page-item " <?php echo $link_active; ?>><a class="page-link  yz-theme-l3" href="javascript:void(0);" style="cursor: pointer" onclick="carinmbrg(<?php echo $i; ?>, false)"><?php echo $i; ?></a></li>
		    <?php
		    }
		    ?>
		    
		    <!-- LINK NEXT AND LAST -->
		    <?php
		    if($page == $jumlah_page || $get_jumlah['jumlah']==0){ // Jika page terakhir
		    ?>
		      <li class="page-item disabled " ><a class="page-link  yz-theme-l1" href="javascript:void(0)" style="cursor: no-drop;padding-left:15px;padding-right:15px"><i class="fa fa-chevron-right"></i></a></li>
		      <li class="page-item disabled "><a class="page-link yz-theme-d1" href="javascript:void(0)" style="cursor: no-drop">Last</a></li>
		    <?php
		    }else{ // Jika Bukan page terakhir
		      $link_next = ($page < $jumlah_page)? $page + 1 : $jumlah_page;
		    ?>
		      <li class="page-item"><a class="page-link yz-theme-l1" href="javascript:void(0)" onclick="carinmbrg(<?php echo $link_next; ?>, false)" style="cursor: pointer;padding-left:15px;padding-right:15px"><i class="fa fa-chevron-right"></i></a></li>
		      <li class="page-item "><a class="page-link yz-theme-d1" href="javascript:void(0)" onclick="carinmbrg(<?php echo $jumlah_page; ?>, false)" style="cursor: pointer">Last</a></li>
		    <?php
		    }
		    ?>
		  </ul>
		</nav>
	</div>

<script>
(function ($) {
    $.fn.enableCellNavigation = function () {
      var arrow = { left: 37, up: 38, right: 39, down: 40 };
      // select all on focus
      this.find('input').keydown(function (e) {
          // shortcut for key other than arrow keys
        if ($.inArray(e.which, [arrow.left, arrow.up, arrow.right, arrow.down]) < 0) { return; }
        var input = e.target;
        var td = $(e.target).closest('td');
        var moveTo = null;
        switch (e.which) {
          case arrow.left: {
              if (input.selectionStart == 0) {
                  moveTo = td.prev('td:has(input,textarea)');
              }
              break;
          }
          case arrow.right: {
              if (input.selectionEnd == input.value.length) {
                  moveTo = td.next('td:has(input,textarea)');
              }
              break;
          }
          case arrow.up:
          case arrow.down: {
              var tr = td.closest('tr');
              var pos = td[0].cellIndex;
              var moveToRow = null;
              if (e.which == arrow.down) {
                  moveToRow = tr.next('tr');
              }
              else if (e.which == arrow.up) {
                  moveToRow = tr.prev('tr');
              }
              if (moveToRow.length) {
                  moveTo = $(moveToRow[0].cells[pos]);
              }
              break;
          }
        }
        if (moveTo && moveTo.length) {
          e.preventDefault();
          moveTo.find('input,textarea').each(function (i, input) {
              input.focus();
              input.select();
          });
        }
      });
    };
  })(jQuery);
  $(function() {
    $('#jnmbrg').enableCellNavigation();
  });    
</script>		
<?php
	$html = ob_get_contents(); // Masukan isi dari view.php ke dalam variabel $html
	ob_end_clean();
	// Buat array dengan index hasil dan value nya $html
	// Lalu konversi menjadi JSON
	echo json_encode(array('hasil'=>$html));
?>