<?php
	$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
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
	  <table id="tabcrew" class="table-bordered table-hover" style="font-size:10pt;background-color: white;width: 100%;border-collapse: collapse;white-space: nowrap;font-size:9pt">
	    <tr align="middle" class="yz-theme-l4" style="background-color: white;position:sticky;top:1px">
	      <th style="width: 2%">NO</th>
	      <th>NAMA CREW</th>
	      <th style="width: 30%">TELP</th>
	    </tr>
	    <?php
	    include "config.php";
      include_once "crew_helper.php";
	    if(!session_id()) session_start();
     	    
	    $con1=opendtcek();
      ensureCrewTable($con1);
      $kd_toko = isset($_SESSION['id_toko']) ? mysqli_real_escape_string($con1, $_SESSION['id_toko']) : '';
      $nm_toko_sesi = isset($_SESSION['nm_toko']) ? mysqli_real_escape_string($con1, $_SESSION['nm_toko']) : '';
      $params = mysqli_real_escape_string($con1, $keyword);

      $sql1 = false;
      if($con1 && $kd_toko !== ''){
        $filter_where = array();
        $filter_where[] = "kd_toko='$kd_toko'";
        $filter_where[] = "aktif=1";
        if($params !== ''){
          $filter_where[] = "(nm_crew LIKE '%$params%' OR kd_crew LIKE '%$params%')";
        }
        $where_sql = " WHERE ".implode(" AND ", $filter_where);
        $sql1 = mysqli_query($con1, "SELECT * FROM crew $where_sql ORDER BY nm_crew ASC");
      }
	    
	    $no=0;
	    while($sql1 && ($databrg = mysqli_fetch_array($sql1))){
	      $no++;
	    ?>
	      <tr>
	        <td style="text-align: right"><?php echo $no?>&nbsp;</td>
	        <td>
	          <input class="w3-input" type="text" readonly value="<?=htmlspecialchars($databrg['nm_crew']); ?>"
	          style="border: none;background-color: transparent;cursor: pointer"
	          onclick="document.getElementById('<?='pilcrew'.$no?>').click();">
	        </td>
	        <td align="left" class="button" style="cursor:pointer;">
	          <input id="<?='pilcrew'.$no?>" class="w3-input" type="text" readonly value="<?=htmlspecialchars(isset($databrg['no_telp']) ? $databrg['no_telp'] : ''); ?>" 
	          style="border: none;background-color: transparent;cursor: pointer"
             onclick="
               document.getElementById('kd_crew_byr').value='<?=mysqli_escape_string($con1,$databrg['kd_crew']) ?>';
               document.getElementById('nm_crewbayar').value='<?=mysqli_escape_string($con1,$databrg['nm_crew']) ?>';
               document.getElementById('viewidcrewbayar').style.display='none';
             ">
	        </td>
	      </tr>
	    <?php
	    }
	    ?>
	  </table>
</div>

<?php
    if($con1){ mysqli_close($con1); }
	$html = ob_get_contents();
	ob_end_clean();
	echo json_encode(array('hasil'=>$html));
?>
