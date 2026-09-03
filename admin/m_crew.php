<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="img/keranjang.png">
<div class="loader1" style="z-index: 10023"><div class="loader2"><div class="loader3"></div></div></div>
<?php 
 include 'starting.php';
 include 'cekmasuk.php';
 include_once 'crew_helper.php';
 $connect=opendtcek();
 ensureCrewTable($connect);
 $nm_toko_sesi = isset($_SESSION['nm_toko']) ? $_SESSION['nm_toko'] : '';
?>

<div id="main" style="font-size: 10pt">
  	<script>	
	  	function cekid(){
	  	  <?php $cek=mysqli_query($connect,"SELECT MAX(no_urut) FROM crew");
	            $max=mysqli_fetch_row($cek); 
		        $id=((int)$max[0])+1; 
	      ?>
	      document.getElementById("no_urut").value='<?=$id?>';
        document.getElementById("kd_crew").value='IDCREW-'+'<?=$id?>';
        <?php if ($cek) { mysqli_free_result($cek); } ?>
		}
			
	  	function kosongkan(){
	      document.getElementById('kd_crew').value="";
	      document.getElementById('nm_crew').value="";
        document.getElementById('al_crew').value="";
        document.getElementById('no_telp').value="";
        document.getElementById('no_urut').value="";
        document.getElementById('aktif').checked = true;
	      document.getElementById('kd_crew').focus();
	 	}  

	 	function caricrew(page_number, search){
		  $.ajax({
		    url: 'm_crewcari.php',
		    type: 'POST',
		    data: {
          keyword: $("#keyktcrew").val(),
          page: page_number,
          search: search,
          sort: $("#sort_crew").val()
        }, 
		    dataType: "text",
		    beforeSend: function(e) {
		      if(e && e.overrideMimeType) {
		        e.overrideMimeType("text/html;charset=UTF-8");
		      }
		    },
		    success: function(response){
		      var html = response;
		      try {
		        var parsed = JSON.parse(response);
		        if (parsed && typeof parsed.hasil !== "undefined") {
		          html = parsed.hasil;
		        }
		      } catch (e) {}
		      $("#viewdtcrew").html(html);
		    },
		    error: function (xhr) {
		      if (window.console) {
		        console.error("Gagal memuat crew:", xhr.status, xhr.responseText);
		      }
		      popnew_error("Gagal memuat data crew");
		    }
		  });
	    }

  $(document).ready(function(){
    $( '.idcrew' ).mask('IDCREW-00000000');
    $( '.telp' ).mask('0000 00000000000');
  });
    </script> 

    <div id="snackbar" style="z-index: 1"></div>
    <?php 
    if(isset($_GET['pesan'])){
      $pesan=$_GET['pesan'];
      if($pesan=="simpan"){
        ?><script>popnew_ok("Data berhasil disimpan");</script><?php
      }else if($pesan=="hapus"){
        ?><script>popnew_warning("Data berhasil dihapus");</script><?php
      }else if($pesan=="gagal"){
        ?><script>popnew_error("Ops.. gagal untuk transaksi");</script><?php
      }
    } 
    ?>
  
    <div class="w3-container w3-card" style="background: linear-gradient(165deg, magenta 0%, yellow 45%, white 85%);position: sticky;top:44px;margin-top: -6px;z-index: 1;">
      <i class='fa fa-briefcase' style="font-size: 18px">&nbsp;MASTER DATA &nbsp;</i> <i class='fa fa-angle-double-right'></i>&nbsp;<span style="font-size: 18px">Crew</span>
    </div>
      <div class="w3-row" style="background: linear-gradient(565deg, #FFFACD 10%, white 90%);">
        <div class="col-sm-12 ">
      	  <form id="form1" class="w3-container" action="m_crew_act.php" method="post">
      	  	<div class="row w3-margin-top">
      	  		<div class="col-sm-6">
      	  		  <div class="form-group row">
      	   	      <label for="kd_crew" class="col-sm-4 col-form-label"><b>Kode Crew</b></label>
      	   	      <div class="col-sm-8">
      	   	        <input class="form-control hrf_arial idcrew" id="kd_crew" type="text" name="kd_crew" autofocus required style="border: 1px solid black;font-size: 10pt;">
      	   	      </div>
        	   	 </div>	
               <div class="form-group row" style="margin-top: -10px" >
                  <input type="hidden" name="no_urut" id="no_urut">
                  <label for="nm_crew" class="col-sm-4 col-form-label"><b>Nama Crew</b></label>
                  <div class="col-sm-8">
                    <input class="form-control hrf_arial" id="nm_crew" type="text" name="nm_crew" required style="border: 1px solid black;font-size: 10pt;">
                  </div>
               </div>
               <div class="form-group row" style="margin-top: -10px">
                 <label for="nm_toko" class="col-sm-4 col-form-label"><b>Nama Toko</b></label>
                 <div class="col-sm-8">
                   <input class="form-control hrf_arial" id="nm_toko" type="text" name="nm_toko" value="<?=htmlspecialchars($nm_toko_sesi)?>" readonly style="border: 1px solid black;font-size: 10pt;background:#eee">
                 </div>
               </div> 
             	</div>
              <script>cekid();</script>
                 
      	  		<div class="col-sm-6 ">
      	  		  <div class="form-group row" >
                  <label for="al_crew" class="col-sm-4 col-form-label"><b>Alamat</b></label>
                  <div class="col-sm-8">
                    <input class="form-control hrf_arial" id="al_crew" type="text" name="al_crew" style="border: 1px solid black;font-size: 10pt;">
                  </div>
                </div> 
                <div class="form-group row" style="margin-top: -10px">
                  <label for="no_telp" class="col-sm-4 col-form-label"><b>No.Telp / HP</b></label>
                  <div class="col-sm-8">
                    <input class="form-control hrf_arial telp" id="no_telp" type="text" name="no_telp" style="border: 1px solid black; font-size: 10pt" >
                  </div>
                </div>
                <div class="form-group row" style="margin-top: -10px">
                  <label class="col-sm-4 col-form-label"><b>Status</b></label>
                  <div class="col-sm-8" style="padding-top:6px">
                    <label style="font-weight:normal;cursor:pointer">
                      <input type="checkbox" id="aktif" name="aktif" value="1" checked>
                      Aktif (tampil di penjualan)
                    </label>
                  </div>
                </div>
      	  		</div>
      	  	</div>
  	        <div class="row">
              <div class="col-sm-6">
                  <button type="submit" style="width: 100%;height:30px;margin-top: 0px;margin-bottom: 0px;border-radius: 4px;font-size: 10pt;" class="w3-margin-bottom w3-card-2 yz-theme-l1"><i class="fa fa-save">&nbsp;&nbsp;</i><b>S I M P A N</b></button>
              </div>	
              <div class="col-sm-6" style="padding-bottom: 2px">
                  <button onclick="kosongkan();cekid()" type="button" style="width: 100%;height:30px;margin-top: 0px;margin-bottom: 0px;border-radius: 4px;font-size: 10pt;" class="w3-margin-bottom btn-warning"><i class="fa fa-undo">&nbsp;&nbsp;</i><b>R E S E T</b></button>
              </div>
            </div>  
      	  </form>	

        	<div class=" yz-theme-l5 w3-border">
            <div class="w3-row">
              <div class="w3-half" >
                <div id="ket_rec" class="fa fa-television" style="margin-top: 15px;margin-left: 10px;font-size: 13pt">  
                </div>
              </div>
              <div class="w3-half">
                <div class="input-group" style="margin-top: 15px">
                  <select id="sort_crew" class="form-control hrf_arial" style="font-size: 10pt;height: 30px;max-width: 165px;margin-right: 4px;" onchange="caricrew(1, true);" title="Urutkan data">
                    <option value="abjad">Abjad A–Z</option>
                    <option value="id_asc">ID Crew</option>
                  </select>
                  <input onkeyup="if(event.keyCode==13){caricrew(1, true);}" style="font-size: 10pt;height: 30px" type="text" class="form-control hrf_arial" placeholder="ketik pencarian [nama crew]" id="keyktcrew">&nbsp;
                  <span class="input-group-btn w3-margin-bottom">
                    <button onclick="caricrew(1, true);" class="btn btn-primary" type="button" id="btn-ktcrew" style="font-size: 10pt;" title="Cari"><i class="fa fa-search"></i></button>
                    <a style="font-size: 10pt;" title="Reset cari" onclick="document.getElementById('keyktcrew').value='';document.getElementById('btn-ktcrew').click();" href="#" class="btn btn-warning"><i class="fa fa-undo"></i></a>
                  </span>
                </div>    
              </div>
            </div>  
          </div>  
          <div class="hrf_arial" id="viewdtcrew" style="margin-top: 0px;"><script>caricrew(1,true)</script></div>
        </div>  
      </div>
</div>
<script>
  $(document).ready(function(){
    $(".loader1").fadeOut();
  })
</script>
