<!DOCTYPE html>
<html lang="id">
  <?php 
   include 'cekmasuk.php';
   include 'config.php';
   setlocale(LC_MONETARY , "ID");
   $kd_toko=$_SESSION['id_toko'];
   $id_user=$_SESSION['id_user'];
  ?> 
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="img/keranjang.png">
    <title>Penjualan Barang</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/w3.css">  
    <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/blue-themes.css">
    <link rel="stylesheet" href="../assets/css/alertyaz.min.css">
    <!-- <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway|Cinzel Decorative&effect=outline|emboss">   -->
    <script src="../assets/js/alertyaz.js"></script>
    <!--<script src="https://cdn.jsdelivr.net/npm/recta/dist/recta.js"></script>-->
     <script src="https://unpkg.com/recta/dist/recta.js"></script>
    <script type="text/javascript" src="../assets/js/jquery-3.3.1.min.js"></script> 
    <script type="text/javascript" src="../assets/js/jquery.mask.min.js"></script> 
    <script type="text/javascript" src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/html5-qrcode.min.js"></script>
  </head>
  <style>
    body,h2,h3,h4,h5,h6 {font-family: "Helvetica", arial}
    body, html {
       /*height: 100%;*/
       line-height: 1.8;
       /*margin-top: 25px;*/
    }
    .hrf_cinzel {font-family: "Cinzel Decorative", Helvetica;}
    .hrf_arial {font-family: Arial, Helvetica;}
    .hrf_helv {font-family: Helvetica, arial;}
    .customb {
      border: 1px solid black;
      width:29px;height: 28px;
      border-radius: 4px;
      cursor: pointer;
      box-shadow: 0px 0px 2px
    }

  th {
    position: sticky;
    top: 0px; 
    box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
    border: 1px solid lightgrey;
    padding: 1px;
  }
  @media only screen and (max-width: 992px) {
    input.hrf_arial {font-size:12pt;}
    input.hrf_res {font-size:12pt;}
  }
  @media only screen and (min-width: 992px) {
    input.hrf_arial {font-size:10pt;}
    input.hrf_res {font-size:9pt;}
  }
</style>

  <body onkeydown="tekantombol()">
    <div id="main" style="font-size: 10pt;background: linear-gradient(565deg, #FAFAD2 10%, white 80%)">
    <div class="loader1" style="z-index: 10023"><div class="loader2"><div class="loader3"></div></div></div>

    <script>        
      function carikd_bar(){
        // $(this).html("ketik pencarian").attr("disabled", "disabled");
        
        $.ajax({
          url: 'f_jual_carikdbar.php', // File tujuan
          type: 'POST', // Tentukan type nya POST atau GET
          data: {kd_bar:$("#kd_bar").val(),
          no_fakjual:$("#no_fakjual").val(),
          tgl_jual:$("#tgl_fakjual").val(),
          tgl_jt:$("#tgl_jt").val(),
          kd_bayar:$("#cr_bay").val(),
          kd_pel:$("#kd_pel").val(),
          no_urutjual:$("#no_urutjual").val()
          }, 
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(response){ 
            // $("#btn-ktcari").html("fa fa-search").removeAttr("disabled");
            
            $("#viewkdbar").html(response.hasil);
          },
          error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
            alert(xhr.responseText); // munculkan alert
          }
        });
      }
        
      function carinmbrg(page_number, search){
        $.ajax({
          url: 'f_jual_caribrg.php', // File tujuan
          type: 'POST', // Tentukan type nya POST atau GET
          data: {keyword:$("#kd_brg").val(), page: page_number, search: search}, 
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(response){ 
            // $("#btn-ktcari").html("fa fa-search").removeAttr("disabled");
            
            $("#viewnmbrg").html(response.hasil);
            $("#viewnmbrgsm").html(response.hasil);
          },
          error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
            alert(xhr.responseText); // munculkan alert
          }
        });
      }

      function caribrgjual(page_number, search){
        
        $.ajax({
          url: 'f_jualcari.php', // File tujuan
          type: 'POST', // Tentukan type nya POST atau GET
          data: {keyword:$("#no_fakjual").val()+';'+$("#tgl_fakjual").val(), page: page_number, search: search}, 
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(response){ 
            // $("#btn-ktcari").html("fa fa-search").removeAttr("disabled");
            
            $("#viewbrgjual").html(response.hasil);
          },
          error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
            alert(xhr.responseText); // munculkan alert
          }
        });
      } 

      function carisatbrg(){
        $.ajax({
          url: 'f_jual_carisat.php', // File tujuan
          type: 'POST', // Tentukan type nya POST atau GET
          data: {keyword:$("#kd_brg").val()}, 
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(response){ 
            // $("#btn-ktcari").html("fa fa-search").removeAttr("disabled");
            
            $("#tabkem").html(response.hasil);
          },
          error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
            alert(xhr.responseText); // munculkan alert
          }
        });
      }

      function getdiscpromo(kd_brg, hrg_jual){
        // Fungsi untuk mengambil discount promo dan mengisi field discitem
        // hrg_jual adalah optional, jika tidak ada akan diambil discount rupiah saja
        if (!kd_brg) {
          return;
        }
        
        var tgl_jual = $("#tgl_fakjual").val();
        if (!tgl_jual) {
          tgl_jual = '<?php echo date('Y-m-d'); ?>';
        }
        
        hrg_jual = hrg_jual || 0;
        
        $.ajax({
          url: 'f_jual_getdisc.php',
          type: 'POST',
          data: {
            kd_brg: kd_brg,
            tgl_jual: tgl_jual,
            hrg_jual: hrg_jual
          },
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(response){ 
            // Jika ada discount, isi field discitem
            if (response && response.discitem && parseFloat(response.discitem) > 0) {
              // Format angka dengan format Indonesia (titik untuk ribuan, koma untuk desimal)
              var discitem_value = parseFloat(response.discitem);
              var formatted_value = formatAngkaIndonesia(discitem_value);
              // Set nilai ke field discitem
              $("#discitem").val(formatted_value);
              // Trigger event input untuk memastikan format ter-update
              $("#discitem").trigger('input');
              $("#discitem").trigger('change');
              // Focus ke field discitem untuk memastikan terlihat
              setTimeout(function() {
                $("#discitem").focus();
              }, 100);
            } else {
              // Jika tidak ada discount, kosongkan field
              $("#discitem").val('');
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            // Jika error, kosongkan field discount
            $("#discitem").val('');
          }
        });
      }

      function formatAngkaIndonesia(angka) {
        // Format angka dengan titik untuk ribuan dan koma untuk desimal (format Indonesia)
        var _minus = false;
        if (angka < 0) {
          _minus = true;
          angka = Math.abs(angka);
        }
        
        var angkaStr = angka.toFixed(2).toString();
        var parts = angkaStr.split('.');
        var bagianBulat = parts[0];
        var bagianDesimal = parts[1] || '00';
        
        // Tambahkan titik sebagai pemisah ribuan
        var formatted = '';
        var panjang = bagianBulat.length;
        for (var i = 0; i < panjang; i++) {
          if (i > 0 && (panjang - i) % 3 === 0) {
            formatted += '.';
          }
          formatted += bagianBulat.charAt(i);
        }
        
        // Gabungkan dengan desimal (gunakan koma)
        var result = formatted + ',' + bagianDesimal;
        if (_minus) {
          result = '-' + result;
        }
        
        return result;
      } 

      function cekjmlstok(kdsatuan,kdbrg){
        $.ajax({
          url: 'f_jual_cekstok.php', // File tujuan
          type: 'POST', // Tentukan type nya POST atau GET
          data: {keyword1:kdsatuan, keyword2: kdbrg, keyword3:$("#no_urutjual").val()}, 
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(response){ 
            // $("#btn-ktcari").html("fa fa-search").removeAttr("disabled");          
            $("#viewjmlstok").html(response.hasil);
          },
          error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
            alert(xhr.responseText); // munculkan alert
          }
        });
      } 

      function carinmpel(){
        $.ajax({
          url: 'f_jualcarinmpel.php', // File tujuan
          type: 'POST', // Tentukan type nya POST atau GET
          data: {keyword:$("#no_fakjual").val()}, 
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(response){ 
            // $("#btn-ktcari").html("fa fa-search").removeAttr("disabled");
            
            $("#viewnmpel").html(response.hasil);
          },
          error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
            alert(xhr.responseText); // munculkan alert
          }
        });
      }

      function cariidpel(page_number, search){
        $.ajax({
          url: 'f_cariidpel.php', // File tujuan
          type: 'POST', // Tentukan type nya POST atau GET
          data: {keyword:$("#kd_pel").val(), page: page_number, search: search}, 
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(response){ 
            // $("#btn-ktcari").html("fa fa-search").removeAttr("disabled");
            
            $("#viewidpel").html(response.hasil);
          },
          error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
            alert(xhr.responseText); // munculkan alert
          }
        });
      }

      function bayarcariidpel(page_number, search){
        $.ajax({
          url: 'f_jualcariidpel.php', // File tujuan
          type: 'POST', // Tentukan type nya POST atau GET
          data: {keyword:$("#nm_pel_byr").val(), page: page_number, search: search}, 
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(response){ 
            // $("#btn-ktcari").html("fa fa-search").removeAttr("disabled");
            
            $("#viewidpelbayar").html(response.hasil);
          },
          error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
            alert(xhr.responseText); // munculkan alert
          }
        });
      } 
        
      function panding(){
        $.ajax({
          url: 'f_jualpanding.php', // File tujuan
          type: 'POST', // Tentukan type nya POST atau GET
          data: {keyword:$("#no_fakjual").val()+';'+$("#tgl_fakjual").val()}, 
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(response){ 
            // $("#btn-ktcari").html("fa fa-search").removeAttr("disabled");
            
            $("#viewhapusnota").html(response.hasil);
          },
          error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
            alert(xhr.responseText); // munculkan alert
          }
        });
      }

      function cekkusus(kd_brg){
        $.ajax({
          url: 'f_jual_cekkusus.php', // File tujuan
          type: 'POST', // Tentukan type nya POST atau GET
          data: {keyword:kd_brg}, 
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(response){ 
            // $("#btn-ktcari").html("fa fa-search").removeAttr("disabled");
            
            $("#viewcekkd").html(response.hasil);
          },
          error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
            alert(xhr.responseText); // munculkan alert
          }
        });
      }

      function carinopanding(page_number, search){
        $.ajax({
          url: 'f_jualcaripanding.php', // File tujuan
          type: 'POST', // Tentukan type nya POST atau GET
          data: {page:page_number,search:search}, 
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(response){ 
            // $("#btn-ktcari").html("fa fa-search").removeAttr("disabled");
            
            $("#viewlistpanding").html(response.hasil);
          },
          error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
            alert(xhr.responseText); // munculkan alert
          }
        });
      } 
      
      function carinotajual(page_number, search){
        $.ajax({
          url: 'f_jualcaribrg.php', // File tujuan
          type: 'POST', // Tentukan type nya POST atau GET
          data: {keyword:$("#keycarijual").val(),page:page_number,search:search}, 
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(response){ 
            // $("#btn-ktcari").html("fa fa-search").removeAttr("disabled");
            
            $("#viewlistjual").html(response.hasil);
          },
          error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
            alert(xhr.responseText); // munculkan alert
          }
        });
      }

      function editakhir(keys){
        $.ajax({
          url: 'f_jualeditakhir.php', // File tujuan
          type: 'POST', // Tentukan type nya POST atau GET
          data: {keyword1:$("#tgl_fakjual").val(),keyword2:$("#no_fakjual").val(),keyword3:keys}, 
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(response){ 
            $("#vieweditakhir").html(response.hasil);
          },
          error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
            alert(xhr.responseText); // munculkan alert
          }
        });
      } 
      function aktif(){
        document.getElementById('tmb-add').type='submit';
        document.getElementById('tmb-add2').type='submit';
        document.getElementById('tmb-add3').type='submit';
      }
      function pasif(){
        document.getElementById('tmb-add').type='button';
        document.getElementById('tmb-add2').type='button';
        document.getElementById('tmb-add3').type='button';
      }

      function tekantombol(){  
        if (event.keyCode==118) {
          event.preventDefault()
          document.getElementById('kd_bar').focus();
          pasif();
          // F7
        }
        if (event.keyCode==119) {
          event.preventDefault()
          // document.getElementById('kd_brg').removeAttribute('disabled',true);
          document.getElementById('kd_brg').focus();
          aktif();    
          // F8
        }
        if (event.keyCode==120) {
          event.preventDefault()
          document.getElementById('tmb-add').click();
          //F9
        }
        if (event.keyCode==121) {
          event.preventDefault()
          document.getElementById('tmb-bayar').click();
          // F10
        }
        if (event.keyCode==123) {
          event.preventDefault()
          document.getElementById('tmb-batal').click();
          // F12
        }
        if (event.keyCode==35) {
          event.preventDefault()
          document.getElementById('btn-nocetak').click();
          // end
        }
        if (event.keyCode==112) {
          event.preventDefault()
          document.getElementById('btn-editsat').click();
          aktif();
          // F1
        }
        if (event.keyCode==113) {
          event.preventDefault()
          document.getElementById('btn-editakhir').click();
          aktif();
          // F2
        }
        if (event.keyCode==114) {
          event.preventDefault()
          document.getElementById('tmb-panding').click();
          // F3
        }
        if (event.keyCode==115) {
          event.preventDefault()
          document.getElementById('tmb-listpanding').click();
          // F4
        }
        if (event.keyCode==36) {
          event.preventDefault()
          document.getElementById('form-warning').style.display='none';
          if (document.getElementById('edit-warning').value==1){
              document.getElementById('kd_bar').focus();
          }
          if (document.getElementById('edit-warning').value==2){
              document.getElementById('kd_brg').focus();
          }
          document.getElementById('edit-warning').value==0;
          // F4
        }
      }
      
      function hapusnota(){
        // $(this).html("ketik pencarian").attr("disabled", "disabled");
        // document.getElementById('fload').style.display='block';    
        $.ajax({
          url: 'f_jualhapusnota_act.php', // File tujuan
          type: 'POST', // Tentukan type nya POST atau GET
          data: {no_fakjual:$("#no_fakjual").val()}, 
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }

          },
          success: function(response){ 
            // $("#btn-ktcari").html("fa fa-search").removeAttr("disabled");
            $("#viewhapusnota").html(response.hasil);
            // document.getElementById('fload').style.display='none';    
          },
          error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
            alert(xhr.responseText); // munculkan alert
            document.getElementById('fload').style.display='none';    
          }
        });
      }

      function hapuspanding(nofakjual){
        $.ajax({
          url: 'f_jualhapusnota_act.php', // File tujuan
          type: 'POST', // Tentukan type nya POST atau GET
          data: {no_fakjual:nofakjual}, 
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }

          },
          success: function(response){ 
            // $("#btn-ktcari").html("fa fa-search").removeAttr("disabled");
            $("#viewhapusnota").html(response.hasil);
            document.getElementById('fload').style.display='none';    
          },
          error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
            alert(xhr.responseText); // munculkan alert
            document.getElementById('fload').style.display='none';    
          }
        });
      }

      function hapusbrg(id){
        $.ajax({
          url: 'f_jualhapus_act.php', // File tujuan
          type: 'POST', // Tentukan type nya POST atau GET
          data: {id:id}, 
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(response){ 
            // $("#btn-ktcari").html("fa fa-search").removeAttr("disabled");
            $("#viewhapusnota").html(response.hasil);
          },
          error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
            alert(xhr.responseText); // munculkan alert
          }
        });
      }

      function startjual(cari){
        $.ajax({
          url: 'f_jualstart.php', // File tujuan
          type: 'POST', // Tentukan type nya POST atau GET
          data: {keyword:cari}, 
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(response){ 
            // $("#btn-ktcari").html("fa fa-search").removeAttr("disabled");
            $("#viewstart").html(response.hasil);
          },
          error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
            alert(xhr.responseText); // munculkan alert
          }
        });
      }
      
      function cekinpotong(kdsat,kdbrg){
        $.ajax({
          url: 'f_jualcekinpotong.php', // File tujuan
          type: 'POST', // Tentukan type nya POST atau GET
          data: {keyword1:kdsat,keyword2:kdbrg}, 
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(response){ 
            // $("#btn-ktcari").html("fa fa-search").removeAttr("disabled");
            $("#viewstart").html(response.hasil);
          },
          error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
            alert(xhr.responseText); // munculkan alert
          }
        });
      }

      function carilistpaket(page_number,search){
        $.ajax({
          url: 'f_jual_listpaket.php', // File tujuan
          type: 'POST', // Tentukan type nya POST atau GET
          data: {page:page_number,search:search}, 
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(response){ 
            // $("#btn-ktcari").html("fa fa-search").removeAttr("disabled");
            $("#viewlistpaket").html(response.hasil);
          },
          error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
            alert(xhr.responseText); // munculkan alert
          }
        });
      } 
      
      function extrackpaket(xparam){
        $.ajax({
          url: 'f_jual_extpaket.php', // File tujuan
          type: 'POST', // Tentukan type nya POST atau GET
          data: {keyword:xparam,
                 tgl_jual  :$("#tgl_fakjual").val(),
                 no_fakjual:$("#no_fakjual").val(),
                 kd_pel    :$("#kd_pel").val(),
                 kd_bayar  :$("#cr_bay").val(),
                 tgl_jt    :$("#tgl_jt").val()}, 
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(response){ 
            // $("#btn-ktcari").html("fa fa-search").removeAttr("disabled");
            $("#viewcekkd").html(response.hasil);
          },
          error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
            alert(xhr.responseText); // munculkan alert
          }
        });
      }    
      
      function setunpanding(fak,tgl){
        $.ajax({
          url: 'f_jual_unpanding.php', // File tujuan
          type: 'POST', // Tentukan type nya POST atau GET
          data: {no_fakjual:fak,
                 tgl_jual  :tgl},
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(response){ 
            // $("#btn-ktcari").html("fa fa-search").removeAttr("disabled");
            $("#viewcekkd").html(response.hasil);
          },
          error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
            alert(xhr.responseText); // munculkan alert
          }
        });
      }    
      
      function cetaknota(dtc,kopi){
        $.ajax({
          url: 'f_jual_cetnota.php', // File tujuan
          type: 'POST', // Tentukan type nya POST atau GET
          data: {dtc:dtc,kopi:kopi}, 
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(response){ 
            $("#viewcetnot").html(response.hasil);
          },
          error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
            alert(xhr.responseText); // munculkan alert
          }
        });
      }
      
      function simpanbyr(dtgl_jual,cno_fakjuals,ckd_pel_byr,ckd_bayar,nbyr_awal,ntot_belanja,nbayar,nkembali,ndisctot,ntdiscitem1,nvoucher,nongkir,cpil_tf,dtgl_jtnota,npil_cetak){
        
        $.ajax({
          url: 'f_jualbayar_act.php', // File tujuan
          type: 'POST', // Tentukan type nya POST atau GET
          data: {tgl_jual:dtgl_jual,no_fakjuals:cno_fakjuals,kd_pel_byr:ckd_pel_byr,kd_bayar:ckd_bayar,byr_awal:nbyr_awal,tot_belanja:ntot_belanja,bayar:nbayar,kembali:nkembali,disctot:ndisctot,tdiscitem1:ntdiscitem1,voucher:nvoucher,ongkir:nongkir,pil_tf:cpil_tf,tgl_jtnota:dtgl_jtnota,pil_cetak:npil_cetak}, 
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(response){ 
            // $("#btn-ktcari").html("fa fa-search").removeAttr("disabled");
            document.getElementById('form-bayar').style.display='none';
           
            $("#viewsimpanbyr").html(response.hasil);

          },
          error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
            alert(xhr.responseText); // munculkan alert
          }
        });
      }

      function cektokos2(){      
        $.ajax({
          url: 'cektokos2.php', // File tujuan
          type: 'POST', // Tentukan type nya POST atau GET
          data: {keyword: $("#cekuser2").val()}, 
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(response){ 
            $("#viewcekkd").html(response.hasil);
          
          },
          error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
            alert(xhr.responseText); // munculkan alert
          }
        });
      }
      function del_oto(nofak){      
        $.ajax({
          url: 'f_jual_nota_oto.php', // File tujuan
          type: 'POST', // Tentukan type nya POST atau GET
          data: {keyword: nofak}, 
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(response){ 
            $("#viewcekkd").html(response.hasil);
          
          },
          error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
            alert(xhr.responseText); // munculkan alert
          }
        });
      }
      function del_oto_i(nourut){      
        $.ajax({
          url: 'f_jual_item_oto.php', // File tujuan
          type: 'POST', // Tentukan type nya POST atau GET
          data: {keyword: nourut}, 
          dataType: "json",
          beforeSend: function(e) {
            if(e && e.overrideMimeType) {
              e.overrideMimeType("application/json;charset=UTF-8");
            }
          },
          success: function(response){ 
            $("#viewcekkd").html(response.hasil);
          
          },
          error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
            alert(xhr.responseText); // munculkan alert
          }
        });
      }
      
      // scan via camera android
      // var html5QrcodeScanner = new Html5QrcodeScanner('qr-reader', { fps: 10, qrbox: 250 }); 
      // var lastResult, countResults = 0;
      // var resultContainer = document.getElementById('qr-reader-results');
      function docReady(fn) {
        // see if DOM is already available
        if (document.readyState === "complete"
          || document.readyState === "interactive") {
          // call on next available tick
          setTimeout(fn, 1);
        } else {
          document.addEventListener("DOMContentLoaded", fn);
        }
      }
      docReady(function () {
        var resultContainer = document.getElementById('qr-reader-results');
        var lastResult, countResults = 0;
        function onScanSuccess(decodedText, decodedResult) {
          if (decodedText !== lastResult) {
              ++countResults;
              lastResult = decodedText;
              // Handle on success condition with the decoded message.
              // console.log(`Scan result ${decodedText}`, decodedResult);
              document.getElementById('kd_bar').value=decodedText;
              // html5QrcodeScanner.clear();
              pasif();
              carikd_bar();
              //lastResult=0;
              document.getElementById('form-scancams').style.display='none';
              //document.getElementById('qr-reader')=remove();
          }
        }
        var html5QrcodeScanner = new Html5QrcodeScanner(
            "qr-reader", { fps: 10, qrbox: 250 });
        html5QrcodeScanner.render(onScanSuccess);
      });

      
    </script> 
   
    <!-- Isi Start -->
    <script>
    startjual("<?=$kd_toko.';'.$id_user?>");
    </script>
    <div id="viewstart"></div>         
    <!--  -->
      
    <div class="yz-theme-d1 w3-card-4" style="z-index: 1; ">
      <div class="w3-row">
        <div class="w3-col m3 w3-hide-small w3-hide-medium">
          <h6 style="margin-top: 7px;color:white;text-shadow: 1px 1px 2px black">
            <i class='fa fa-cart-arrow-down'></i> &nbsp;TRANSAKSI &nbsp;
            <i class='fa fa-angle-double-right'></i>&nbsp;
            <span>Penjualan Barang</span>
          </h6> 
        </div>
        <div class="w3-col s4 m7">
        <input type="hidden" id="cekuser2" value="<?=$_SESSION['nm_user']?>">  
          <a id="tmb-info" class="btn w3-text-white w3-right w3-hover-shadow">
            <span style="text-shadow: 1px 1px 2px black"><?=gantitgl(date('Y-m-d'));?>&nbsp;<i class="fa fa-database"></i></span>
            <span id="not_klr" class="w3-badge w3-tiny" style="position: absolute;top: 0px;background-color: blue;  color: white;"></span>
            <div class="w3-container w3-card-4" id="info"  style="z-index: 1000;display:none ;position: absolute;top:45px;
              background-color: rgba(0, 0, 0, 0.8);border-radius: 5px">
                <table class="table table-bordered table-sm table-hover w3-margin-top w3-text-white" style="font-size:10pt;z-index: 1000">
                  <tr ><td colspan='2' align="middle"> <i class="fa fa-2x fa-users w3-text-cyan"></i>&nbsp; <?=$_SESSION['nm_user']. ' # '. $kd_toko?></td></tr>
                  <tr><td id="brg_kel" style="text-align: left"><img src="img/krjy.png" alt="">&nbsp; Jml barang terjual  </td><td id="brg_kel1" align="right" style="padding-top: 12px"></td></tr>    
                  <tr><td style="text-align: left"><img src="img/dmpy.png" alt="">&nbsp; Total Omset Rp.      </td><td align="right" id="tot_om" style="padding-top: 12px"></td></tr>    
                  <tr><td style="text-align: left"><img src="img/dlry.png" alt="">&nbsp; Total Laba  Rp.      </td><td align="right" id="tot_lab" style="padding-top: 12px"></td></tr>
                </table>
              </div>
          </a>
            <script>
                $(document).ready(function(){
                  $("#tmb-info").click(function(){
                    $("#info").slideToggle("fast");
                    // $("#boxkem").slideUp("fast");
                  });  
                  $("#tmb-info").blur(function(){
                    $("#info").slideUp("fast");
                    // $("#boxkem").slideUp("fast");
                  });  
                });
            </script>
        </div>
        <div class="w3-col s8 m2">
            <a href="dasbor.php" class="w3-right w3-text-orange btn w3-hover-shadow w3-wide" style="margin-top: 0px;text-shadow: 1px 1px 2px black"><i class="fa fa-desktop"></i> Exit</a> 
        </div>
      </div>
    </div>

    <button type="button" style="display: none" id="btn-editakhir" onclick="editakhir(1);">edit</button>
    <button type="button" style="display: none" id="btn-editsat" onclick="editakhir(2);">edit</button>

    <form id="form1" action="f_jual_act.php" method="post" style="font-size: 10pt"> 
      <div class="w3-row">
        <div class="w3-col s12 m12 l4 w3-container w3-hide-small w3-hide-medium" style="border-style: ridge;border-color: white;height:147px">
          <div class="w3-row">
            <!--Untk review data  -->
            <input type="hidden" id="vcari" name="vcari">
            <input type="hidden" id="kd_kat" name="kd_kat">
            <input type="hidden" name="no_urutjual" id="no_urutjual">
            <input type="hidden" id="edit-warning" value=0>   
            <!-- <input type="text" id="id_bag" name="id_bag">    -->
            <!--  -->

            <div class="w3-col m6 l12 ">
              <div class="form-group row w3-margin-top">
                <label for="tgl_fakjual" class="col-sm-4 col-form-label" ><b>Tanggal</b></label>
                <div class="col-sm-8">
                  <input id="tgl_fakjual" onblur="caribrgjual(1,true)"  style="border: 1px solid black;" type="date" class="form-control hrf_arial" name="tgl_fakjual" value="<?php echo $_SESSION['tgl_set']; ?>" required autofocus tabindex="1" >
                </div>
              </div> 

              <div class="form-group row" style="margin-top: -10px">
                <label for="tgl_fakjual" class="col-sm-4 col-form-label" ><b>No.Nota</b></label>
                <div class="col-sm-8">
                  <div class="input-group">
                  <input id="no_fakjual" onblur="caribrgjual(1,true);" style="border: 1px solid black;" type="text" class="form-control hrf_arial" name="no_fakjual" required tabindex="2" >
                    <span> 
                      <button type="button" class="tooltips w3-hover-shadow btn yz-theme-d2" onclick="document.getElementById('fcari_jual').style.display='block';carinotajual(1,true);" style="padding-right:10px;" title="cari nota jual"><i class="fa fa-sticky-note-o" style="text-shadow:1px 1px 1px black" ></i>
                      <span class="tooltiptexts w3-text-blue" style="font-size:9pt;"><b>Nota jual</b></span>
                      </button>
                    </span>
                  </div>
                </div>
              </div>    

              <div class="form-group row "style="margin-top: -10px">
              <label for="kd_bar" class="col-sm-4 col-form-label" ><b>Barcode</b></label>
                <div class="col-sm-8">
                  <input id="kd_bar" name="kd_bar" placeholder="Barcode [F7]" onmouseover="this.focus()" onkeypress="
                    if (event.keyCode==13){
                      pasif();
                      carikd_bar();
                    }" type="text" class="form-control hrf_arial" tabindex="2" style="border: 1px solid black;">
                </div>
              </div>    
            </div><!-- Col-sm-3 -->
            
            <input id="kd_pel" name="kd_pel" onkeyup="cariidpel(1, true);" type="hidden" class="form-control hrf_arial" required style="border: 1px solid black;" tabindex="3"> 
            <input id="tgl_jt" style="border: 1px solid black;" type="hidden" class="form-control hrf_arial w3-margin-bottom" name="tgl_jt" tabindex="5" >

            <select class="form-control" name="cr_bay" id="cr_bay" style="display:none;border: 1px solid black;font-size:12px ;height: 30px" required="" tabindex="4" onclick="document.getElementById('kd_bayar2').value=this.value">
                    <option value="TUNAI">TUNAI</option>
                    <option value="TEMPO">TEMPO</option>
                  </select>

            
          </div><!-- w3-row -->
        </div><!-- w3-col 12 -->

        <div class="w3-col s12 m12 l8" style="border-style: ridge;border-color: white">
          <div class="w3-row w3-container">
            <div class="w3-col s12 m12 l12 hrf_arial">
              <div id="angka_bay1" class="w3-xxlarge w3-hide-medium w3-hide-large">
              </div>
              <div id="angka_bay2" class="w3-jumbo w3-hide-small" style="text-align: center;margin-top: -14px">
              </div>
              
              <div id="keterangan1" class="hrf_helv w3-hide-small w3-margin-bottom" style="font-size: 14px;text-align: center">
              </div>
              <div id="keterangan2" class="hrf_helv w3-hide-medium w3-hide-large w3-margin-bottom" style="font-size: 14px">
              </div>
            </div><!-- Col-12 -->
          </div>
        </div>
      
      </div><!-- row -->

      <!-- tombol -->
      <div class="w3-row w3-section">    
        
        <!--SMALL SCREEN  untuk tombol--> 
        <div class="w3-col s12 w3-margin-bottom  w3-hide-medium w3-hide-large w3-center" >
          <!-- NOTA JUAL -->
          <button type="button" class="tooltips w3-hover-shadow btn btn-md yz-theme-d2"   
            onclick="document.getElementById('fcari_jual').style.display='block';carinotajual(1,true);" style="border-radius:50%;width:38px;padding-right:7px;border:2px solid white;" title="cari nota jual"><i class="fa fa-sticky-note-o" style="margin-left:-4px;text-shadow:1px 1px 1px black" ></i><span class="tooltiptexts w3-text-blue" style="font-size:9pt;"><b>Nota jual</b></span>
          </button>

          <button id="tmb-panding" type="button" class="tooltips 
           yz-theme-l2 w3-hover-shadow btn btn-md" onclick="if(confirm('Panding Nota?')){panding();}" tabindex="17" style="border: 2px solid white;border-radius:50%;width:39px">
            <i class="fa fa-pause" style="text-shadow:1px 1px 2px black;color:white"></i>
            <span class="tooltiptexts w3-text-blue" style="font-size:9pt"><b>Panding Nota</b></span>
          </button>
          <button id="tmb-listpanding" type="button" class="tooltips 
            yz-theme-l2 w3-hover-shadow btn btn-md" onclick="carinopanding(1,true);document.getElementById('form-panding').style.display='block';" tabindex="18" style="border: 
            2px solid white;border-radius:50%;width:38px;">
            <i class="fa fa-reorder" style="text-shadow:1px 1px 2px black;color:white"></i>
            <span class="tooltiptexts w3-text-blue" style="font-size:9pt"><b>List panding</b></span>
          </button>

          <button type="button" id="paketcl" 
            class="tooltips w3-hover-shadow w3-text-white yz-theme-l1 btn btn-md" onclick="carilistpaket(1,true);document.getElementById('form-paket').style.display='block';" 
            tabindex="14" style="border: 2px solid white;border-radius:50%;width:38px;padding-left:9px">
            <i class="fa fa-briefcase" style="text-shadow:1px 1px 2px black;color:white;"></i><span class="tooltiptexts w3-text-blue" style="color: black;font-size:9pt"><b>List Paket</b></span>
          </button>
          <button id="tmb-batal" type="button" class="tooltips 
             w3-hover-shadow btn btn-md btn-danger" onclick="if(confirm('Nota akan dibatalkan??')){hapusnota();}" tabindex="19" style="border: 2px solid white;border-radius:50%;width:38px;"><i class="fa fa-trash" style="text-shadow:1px 1px 2px black"></i>
            <span class="tooltiptexts" style="font-size:9pt;color:red"><b>Batalkan nota</b></span>
          </button>

          <button id="tmb-reset" type="button" class="tooltips 
            w3-text-black w3-yellow w3-hover-shadow btn btn-md"  tabindex="20" onclick="kosongkan2()" style="border: 2px solid white;border-radius:50%;width:38px;">
            <i class="fa fa-undo" ></i><span class="tooltiptexts" style="color: black;font-size:9pt;color:red"><b>Reset input</b></span>
          </button>
        </div>
        <!-- END SMALL SCREEN -->
        
        <!--MEDIUM SCREEN  untuk tombol--> 
        <div class="w3-col s12 w3-margin-bottom  w3-hide-small w3-hide-large w3-center" >
          <!-- NOTA JUAL -->
          <button type="button" class="tooltips w3-hover-shadow btn btn-md yz-theme-d2"   
            onclick="document.getElementById('fcari_jual').style.display='block';carinotajual(1,true);" style="border-radius:50%;width:38px;padding-right:7px;border:2px solid white;" title="cari nota jual"><i class="fa fa-sticky-note-o" style="margin-left:-4px;text-shadow:1px 1px 1px black" ></i><span class="tooltiptexts w3-text-blue" style="font-size:9pt;"><b>Nota jual</b></span>
          </button>

          <button id="tmb-panding" type="button" class="tooltips 
           yz-theme-l2 w3-hover-shadow btn btn-md" onclick="if(confirm('Panding Nota?')){panding();}" tabindex="17" style="border: 2px solid white;border-radius:50%;width:39px">
            <i class="fa fa-pause" style="text-shadow:1px 1px 2px black;color:white"></i>
            <span class="tooltiptexts w3-text-blue" style="font-size:9pt"><b>Panding Nota</b></span>
          </button>
          <button id="tmb-listpanding" type="button" class="tooltips 
            yz-theme-l2 w3-hover-shadow btn btn-md" onclick="carinopanding(1,true);document.getElementById('form-panding').style.display='block';" tabindex="18" style="border: 
            2px solid white;border-radius:50%;width:38px;">
            <i class="fa fa-reorder" style="text-shadow:1px 1px 2px black;color:white"></i>
            <span class="tooltiptexts w3-text-blue" style="font-size:9pt"><b>List panding</b></span>
          </button>

          <button type="button" id="paketcl" 
            class="tooltips w3-hover-shadow w3-text-white yz-theme-l1 btn btn-md" onclick="carilistpaket(1,true);document.getElementById('form-paket').style.display='block';" 
            tabindex="14" style="border: 2px solid white;border-radius:50%;width:38px;padding-left:9px">
            <i class="fa fa-briefcase" style="text-shadow:1px 1px 2px black;color:white;"></i><span class="tooltiptexts w3-text-blue" style="color: black;font-size:9pt"><b>List Paket</b></span>
          </button>
          <button id="tmb-batal" type="button" class="tooltips 
             w3-hover-shadow btn btn-md btn-danger" onclick="if(confirm('Nota akan dibatalkan??')){hapusnota();}" tabindex="19" style="border: 2px solid white;border-radius:50%;width:38px;"><i class="fa fa-trash" style="text-shadow:1px 1px 2px black"></i>
            <span class="tooltiptexts" style="font-size:9pt;color:red"><b>Batalkan nota</b></span>
          </button>

          <button id="tmb-reset" type="button" class="tooltips 
            w3-text-black w3-yellow w3-hover-shadow btn btn-md"  tabindex="20" onclick="kosongkan2()" style="border: 2px solid white;border-radius:50%;width:38px;">
            <i class="fa fa-undo" ></i><span class="tooltiptexts" style="color: black;font-size:9pt;color:red"><b>Reset input</b></span>
          </button>
        </div>
        <!-- END Medium SCREEN -->
      
        <div class="w3-col s12 m12 l3 w3-margin-bottom pl-2 pr-2">
          <div class="input-group">
          <input id="kd_brg" name="kd_brg" onclick="aktif();carinmbrg(1, true);" 
          onkeyup="aktif();carinmbrg(1, true);"
          onkeydown="
          if(event.keyCode==40){document.getElementById('viewnmbrg').style.display='block';} 
          if(event.keyCode==38){document.getElementById('viewnmbrg').style.display='none';}
          if(event.keyCode===13){event.preventDefault();aktif();carinmbrg(1, true);document.getElementById('viewnmbrg').style.display='block';document.getElementById('viewnmbrgsm').style.display='block'}"  type="text" class="form-control hrf_arial" placeholder="Nama Barang [F8]" required  style="border: 1px solid black;" tabindex="7" autofocus aria-describedby="button-addon2">
          <button class="btn btn-sm btn-outline-primary" type="button" id="button-addon2" onclick="
            document.getElementById('form-scancams').style.display='block'; 
            "><i class="fa fa-camera"></i></button>
          </div>

          <!-- Cari nama barang on large sreen -->
          <div class="w3-row w3-hide-medium w3-hide-small">
            <div id="viewnmbrg" class="w3-card-4 w3-col" style="background-color: white;display: none;position: absolute;z-index: 1;width: 600px;"><script>carinmbrg(1,true)</script>
            </div>
          </div>              

          <script>
            $(document).ready(function(){
              $("#kd_bar").focus(function(){
                $("#viewnmbrg").slideUp("fast");
                $("#tabkem").slideUp("fast");
                $("#viewidpel").slideUp("fast");
              });
              $("#kd_brg").click(function(){
                // $("#viewnmbrg").slideToggle("fast");
                // $("#viewnmbrgsm").slideToggle("fast");
                $("#tabkem").slideUp("fast");
                $("#viewidpel").slideUp("fast");
              });
              $("#kd_brg").keyup(function(){
                if(document.getElementById('kd_brg').value !=""){
                  $("#viewnmbrg").slideDown("fast");
                  $("#viewnmbrgsm").slideDown("fast");
                }else{
                  $("#viewnmbrgsm").slideUp("fast");
                }
              });
              $("#viewnmbrg").mouseleave(function(){
                $("#viewnmbrg").slideUp("fast");
                $("#viewnmbrgsm").slideUp("fast");
              });
              $("#kd_brg").focus(function(){
                  
                $("#viewidpel").slideUp("fast");
                $("#tabkem").slideUp("fast");
              });
            });
          </script> 
        </div>

        <!--Cari nm barang small & medium sreen  -->
        <div class="w3-hide-large w3-row ">
          <div id="viewnmbrgsm" class="w3-card-4 w3-col" style="background-color: white;position:absolute;z-index: 1000;width: 100%;display:none;"><script>carinmbrg(1,true)</script>
          </div>
        </div>
        <!--  -->

        <div class="w3-col s4 m4 l1 w3-margin-bottom pr-2">
          <input id="nm_sat" onkeyup="carisatbrg(1,true);" type="text" style="border: 1px solid black; " class="form-control hrf_arial" name="nm_sat" required="" placeholder="Satuan" tabindex="8">
          <input type="hidden" name="kd_sat" id="kd_sat">
          <!-- box search kemasan -->
          <div class="w3-row">
            <div id="tabkem" class="w3-card-4" style="background-color: white;display: none;position: absolute;z-index: 1"><script>carisatbrg(1,true);</script></div>
          </div> 
          
          <script>
            $(document).ready(function(){
              $("#nm_sat").click(function(){
                $("#tabkem").slideToggle("fast");
                $("#viewnmbrg").slideUp("fast");
                $("#viewidpel").slideUp("fast");
              });
              $("#nm_sat").keyup(function(){
                $("#tabkem").slideDown("fast");
              });
              $("#tabkem").mouseleave(function(){
                $("#tabkem").slideUp("fast");
              });
              $("#nm_sat").focus(function(){
                $("#viewnmbrg").slideUp("fast");
                $("#viewidpel").slideUp("fast");
              });
            });
          </script>
        </div>
        <div class="w3-col s4 m4 l1 w3-margin-bottom pr-2">
          <div id="viewjmlstok"></div>
          <input id="qty_brg" name="qty_brg" type="number" 
          onfocus="document.getElementById('tabkem').style.display='none';"   
            class="form-control hrf_arial" required tabindex="9" style="border: 1px solid black;" placeholder="jml.Brg">
        </div>    
        <div class="w3-col s4 m4 l1 w3-margin-bottom pr-2">
          <input id="discitem" name="discitem" type="text" class="form-control hrf_arial money" required tabindex="10" style="border: 1px solid black;" value="" placeholder="Disc">
        </div>
        <div class="w3-col s12 m12 l2 w3-margin-bottom pr-2" >
          <input id="ketjual" name="ketjual" value="-" type="text" value="-" class="form-control hrf_arial" required="" placeholder="Keterangan" style="border: 1px solid black;" tabindex="11">
        </div>
        
        <!--buttom add screen small  -->
        <div class="w3-col w3-hide-medium w3-hide-large w3-center w3-margin-bottom">
          <!-- TAMBAH KE NOTA -->
          <button id="tmb-add" type="submit" class="tooltips yz-theme-d1 w3-text-white w3-hover-shadow btn btn-md " 
              tabindex="15" style="border: 2px solid white;border-radius:20%;width:38px;"><i class="fa fa-plus" style="text-shadow:1px 1px 2px black;color:yellow"></i>
              <span class="tooltiptexts w3-text-blue" style="font-size:9pt"><b>Tambah ke nota</b></span>
          </button>
           &NonBreakingSpace; 
          <!-- BAYAR NOTA -->
          <button id="tmb-bayar" type="button" 
            onclick="document.getElementById('form-bayar').style.display='block';
                      document.getElementById('bayar').focus();" 
            class="tooltips w3-text-blue w3-hover-shadow btn btn-md yz-theme-d1" 
            tabindex="16" style="border: 2px solid white;border-radius:20%;width:38px;">
            <i class="fa fa-file-text-o " style="text-shadow:1px 1px 2px black;color:yellow"></i>
            <span class="tooltiptexts w3-text-blue" style="color: black;font-size:9pt"><b>Bayar Nota</b></span>
          </button>
        </div>
        
        <!--MEDIUM SCREEN ADD tombol -->
        <div class="w3-col m12 m12 l3 w3-margin-bottom w3-container w3-hide-large w3-hide-small w3-center" >
          <!-- TAMBAH KE NOTA -->
          <button id="tmb-add2" type="submit" class="tooltips yz-theme-d1 w3-text-white w3-hover-shadow btn btn-md " 
              tabindex="15" style="border: 2px solid white;border-radius:20%;width:38px;"><i class="fa fa-plus" style="text-shadow:1px 1px 2px black;color:yellow"></i>
              <span class="tooltiptexts w3-text-blue" style="font-size:9pt"><b>Tambah ke nota</b></span>
          </button>
           &NonBreakingSpace; 
          <!-- BAYAR NOTA -->
          <button id="tmb-bayar" type="button" 
            onclick="document.getElementById('form-bayar').style.display='block';
                      document.getElementById('bayar').focus();" 
            class="tooltips w3-text-blue w3-hover-shadow btn btn-md yz-theme-d1" 
            tabindex="16" style="border: 2px solid white;border-radius:20%;width:38px;">
            <i class="fa fa-file-text-o " style="text-shadow:1px 1px 2px black;color:yellow"></i>
            <span class="tooltiptexts w3-text-blue" style="color: black;font-size:9pt"><b>Bayar Nota</b></span>
          </button>

          <!-- <button id="tmb-add2" type="submit"      
            class="tooltips w3-blue customb w3-hover-shadow " tabindex="12" style="width: 105px;font-size: 9pt"><i class="fa fa-cart-plus"></i> &nbsp;ADD [F9] 
          </button>

          <button id="tmb-bayar" type="button" 
            onclick="document.getElementById('form-bayar').style.display='block';
                      document.getElementById('bayar').focus();" 
            class="tooltips w3-green customb w3-hover-shadow " tabindex="13" style="width: 105px;font-size: 9pt"><i class="fa fa-money">&nbsp; BAYAR</i>
          </button>

          <button id="tmb-panding" type="button" class="tooltips w3-purple customb w3-hover-shadow " onclick="if(confirm('Panding Nota?')){panding();}" tabindex="14" style="width: 105px;font-size: 9pt"><i class="fa fa-archive">&nbsp;PANDING</i>
          </button>
          
          <button id="tmb-listpanding" type="button" class="tooltips w3-deep-purple customb w3-hover-shadow " onclick="carinopanding(1,true);document.getElementById('form-panding').style.display='block';" tabindex="15" style="width: 105px;font-size: 9pt"><i class="fa fa-address-book-o">&nbsp;LIST</i>
          </button>
          
          <button id="tmb-batal" type="button" class="tooltips w3-red customb w3-hover-shadow " onclick="if(confirm('Nota akan dibatalkan??')){hapusnota();}" tabindex="16" style="width: 105px;font-size: 8pt;margin-left: 10px"><i class="fa fa-trash">&nbsp;HAPUS NOTA</i>
          </button>

          <button id="tmb-reset" type="button" class="tooltips w3-yellow customb w3-margin-left"  tabindex="17" onclick="kosongkan2()">
          <i class="fa fa-undo"></i>
          </button>   -->
        </div>
        <!-- END MEDIUM SCREEN tombol-->
        
        <!-- ON LARGE SCREEN tombol-->
        <div class="w3-col l3 w3-hide-small w3-hide-medium w3-right" style="margin-left:15px;margin-top:-5px">
        
          <!-- Button Paket -->
          <button type="button" id="paket" class="tooltips w3-hover-shadow w3-text-white yz-theme-l1 btn btn-md" onclick="carilistpaket(1,true);document.getElementById('form-paket').style.display='block';" 
          tabindex="14" style="border: 2px solid white;border-radius:50%;width:38px;padding-left:9px">
          <i class="fa fa-briefcase" style="text-shadow:1px 1px 2px black;color:white;"></i><span class="tooltiptexts w3-text-blue" style="color: black;font-size:9pt"><b>List Paket</b></span>
          </button>

          <button id="tmb-add3" type="submit" 
            class="tooltips yz-theme-d1 w3-text-white w3-hover-shadow btn btn-md"
            tabindex="15" style="border: 2px solid white;border-radius:50%;width:38px;"><i class="fa fa-plus" style="text-shadow:1px 1px 2px black;color:yellow"></i>
            <span class="tooltiptexts w3-text-blue" style="font-size:9pt"><b>Tekan F9<br>Tambah ke nota</b></span>
          </button>

          <button id="tmb-bayar" type="button" 
            onclick="document.getElementById('form-bayar').style.display='block';
                      document.getElementById('bayar').focus();" 
            class="tooltips w3-text-blue w3-hover-shadow btn btn-md yz-theme-d1" 
            tabindex="16" style="border: 2px solid white;border-radius:50%;width:38px;">
            <i class="fa fa-file-text-o" style="text-shadow:1px 1px 2px black"></i>
            <span class="tooltiptexts w3-text-blue" style="color: black;font-size:9pt"><b>Tekan F10<br>Bayar Nota</b></span>
          </button>              

          <button id="tmb-panding" type="button" class="tooltips 
           yz-theme-l2 w3-hover-shadow btn btn-md" onclick="if(confirm('Panding Nota?')){panding();}" tabindex="17" style="border: 2px solid white;border-radius:50%;width:39px">
            <i class="fa fa-pause" style="text-shadow:1px 1px 2px black;color:white"></i>
            <span class="tooltiptexts w3-text-blue" style="font-size:9pt"><b>Tekan F3<br>Panding Nota</b></span>
          </button>
          
          <button id="tmb-listpanding" type="button" class="tooltips 
            yz-theme-l2 w3-hover-shadow btn btn-md" onclick="carinopanding(1,true);document.getElementById('form-panding').style.display='block';" tabindex="18" style="border: 
            2px solid white;border-radius:50%;width:38px;">
            <i class="fa fa-reorder" style="text-shadow:1px 1px 2px black;color:white"></i>
            <span class="tooltiptexts w3-text-blue" style="font-size:9pt"><b>Tekan F4<br>List panding</b></span>
          </button>
          
          <button id="tmb-batal" type="button" class="tooltips 
             w3-hover-shadow btn btn-md btn-danger" onclick="if(confirm('Nota akan dibatalkan??')){hapusnota();}" tabindex="19" style="border: 2px solid white;border-radius:50%;width:38px;"><i class="fa fa-trash" style="text-shadow:1px 1px 2px black"></i>
            <span class="tooltiptexts" style="font-size:9pt;color:red"><b>Tekan F12<br>Batalkan nota</b></span>
          </button>

          <button id="tmb-reset" type="button" class="tooltips 
            w3-text-black w3-yellow w3-hover-shadow btn btn-md"  tabindex="20" onclick="kosongkan2()" style="border: 2px solid white;border-radius:50%;width:38px;">
            <i class="fa fa-undo" ></i><span class="tooltiptexts" style="color: black;font-size:9pt;color:red"><b>Reset input</b></span>
          </button>
        </div> 
      </div><!-- w3-section -->

      <!-- cek kd kusus -->
      <div id="viewcekkd"></div> 
    </form>
        
    <script type="text/javascript">
      $(document).ready(function() {
        $('#form1').submit(function() {
          $.ajax({
              type: 'POST',
              url: $(this).attr('action'),
              data: $(this).serialize(),
              dataType: "json",
              beforeSend: function(e) {
                if(e && e.overrideMimeType) {
                  e.overrideMimeType("application/json;charset=UTF-8");
                }
              },
              success: function(response) {
                  kosongkan();              
                  $('#viewhapusnota').html(response.hasil);
              },
              error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
                alert(xhr.responseText); // munculkan alert
              }
              
          })
          return false;
        });
      })
    </script> 
    <div id="viewkdbar"></div>
    <div id="viewhapusnota"></div> 
    <div id="vieweditakhir"></div>
    <div id="viewcetnot"></div>
    <div id="viewsimpanbyr"></div>
    <div id="viewbrgjual" style="margin-top: -20px"><script>caribrgjual(1,true);</script></div>
      
      <!-- Scan camera -->
    <div id="form-scancams" class="w3-modal" style="margin-left:0px;background-color:rgba(1, 1, 1, 0.3) ">
      <div class="w3-modal-content w3-card-4 w3-animate-zoom" style="border-radius:5px;background: linear-gradient(565deg, #E6E6FA 0%, white 80%);box-shadow: 0px 2px 60px;border-style: ridge;border-color:white;width: 500px">
        <div class="w3-center w3-padding-small yz-theme-d1 w3-wide">
          <center><i class="fa fa-server"></i>SCAN CAMERA</center>
        </div>
        <!-- <span onclick="" class="close  w3-display-topright" title="Close Modal" style="margin-top: 0px;margin-right: 0px;z-index: 1"><img style="width: 108%" src="img/tomexit2.png" alt=""></span>   -->
        <div class="w3-center">
          <div id="qr-reader"></div>
          <div id="qr-reader-results"></div>
          <button class="btn btn-md btn-warning w3-center" type="button" onclick="document.getElementById('form-scancams').style.display='none'">Close</button>
        </div>  
      </div>
    </div>  
    <script>
      var a=0;
      if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)){
        a=1;
      }else{a=0;}
      if (a==0){
        document.getElementById('qr-reader').remove();
      }
    </script>
    
      <!-- Form nota panding-->
      <div id="form-panding" class="w3-modal" style="padding-top:50px;background-color:rgba(1, 1, 1, 0.3);border-style: ridge; ">
        <div class="w3-modal-content w3-card-4 w3-animate-right" style="max-width:600px;border-radius:5px;box-shadow: 0px 2px 60px;border-style: ridge;border-color:white">

          <div style="background-color: orange;border-style: ridge;border-color: white;background: linear-gradient(165deg, darkblue 0%,cyan 50%,white 80%);color:white;">&nbsp;<i class="fa fa-search"></i>
            Daftar panding nota penjualan
          </div>
    
          <div class="w3-center">
            <span onclick="document.getElementById('form-panding').style.display='none'" class="w3-display-topright" title="Close Modal" style="margin-top: -3px;margin-right: 0px"><img style="width: 108%" src="img/tomexit2.png" alt=""></span>    
          </div>
          <div id="viewlistpanding"><script>carinopanding(1,true);</script></div> 
          
        </div><!--Modal content-->
      </div>
      <!-- End Form Nota -->  

      <!-- Form list paket-->
      <div id="form-paket" class="w3-modal" style="padding-top:50px;background-color:rgba(1, 1, 1, 0.3);border-style: ridge; ">
        <div class="w3-modal-content w3-card-4 w3-animate-top" style="max-width:400px;border-radius:5px;box-shadow: 0px 2px 60px;border-style: ridge;border-color:white">

          <div style="background-color: orange;border-style: ridge;border-color: white;background: linear-gradient(165deg, darkblue 0%,cyan 50%,white 80%);color:white;">&nbsp;<i class="fa fa-search"></i>
            Daftar List Paket
          </div>
    
          <div class="w3-center">
            <span onclick="document.getElementById('form-paket').style.display='none'" class="w3-display-topright" title="Close Modal" style="margin-top: -3px;margin-right: 0px"><img style="width: 108%" src="img/tomexit2.png" alt=""></span>    
          </div>
          <div id="viewlistpaket"><script>carilistpaket(1,true);</script></div> 
          
        </div><!--Modal content-->
      </div>
      <!-- End Form Nota -->  

      <!-- Form WARNING-->
      <div id="form-warning" class="w3-modal" style="padding-top:50px;background-color:rgba(1, 1, 1, 0.1);border-style: ridge; ">
        <div class="w3-modal-content w3-card-4 w3-animate-top" style="max-width:400px;border-radius:5px;box-shadow: 0px 2px 60px;border-style: ridge;border-color:white;background-color:rgba(1, 1, 1, 0.6)">
          <div class="w3-center">
            <span onclick="document.getElementById('form-warning').style.display='none';
            if (document.getElementById('edit-warning').value==1){
              document.getElementById('kd_bar').focus();
            }
            if (document.getElementById('edit-warning').value==2){
                document.getElementById('kd_brg').focus();
            }
            document.getElementById('edit-warning').value==0;
            " class="w3-display-topright" title="Close Modal" style="margin-top: -3px;margin-right: 0px;"><img style="width: 108%;cursor:pointer" src="img/tomexit2.png" alt=""></span> 
          </div>
          <br>
          <div class="w3-center">
            <i class="fa fa-exclamation fa-3x fa-border text-danger" style="text-shadow: 1px 1px 4px #000000;"></i>

            <div class="w3-center" style="font-size: 12pt;color: yellow;">
              <input class="form-control" id="igt-txt1" type="text" readonly style="border:none;background: transparent;color: yellow;text-align: center">
              <input class="form-control" id="igt-txt2" type="text" readonly  autofocus style="border:none;background: transparent;color: yellow;text-align: center;font-size: 10pt;">
            </div>
          </div>
          <br>

        </div><!--Modal content-->

      </div>
      <!-- End Form warning -->  
      
      <div id="fpilprint" class="w3-modal" style="padding-top:60px;margin-left:0px;background-color:rgba(1, 1, 1, 0.3) ">
        <div class="w3-modal-content w3-card-4 w3-animate-zoom" style="border-style: ridge;border-color: white;width:300px ">
          <div  class="yz-theme-d1" style="font-size: 14px;padding:4px">
            &nbsp; <i class="fa fa-print"></i>&nbsp;Silahkan Pilih Printer
            <span onclick="document.getElementById('fpilprint').style.display='none'" class="w3-display-topright" title="Close Form" style="margin-top: -2px;margin-right: 0px;cursor: pointer"><img style="width: 108%" src="img/tomexit2.png" alt=""></span>    
          </div>
          <div class="w3-container w3-padding-large">
            <form id="form-cet" action="f_jual_set_print.php" method="POST">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="pilprint" id="pilprint1" value='CETAK' checked>
                <label class="form-check-label" for="pilprint1">
                  Printer thermal ukuran 80 inc
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="pilprint" id="pilprint2" value='CETAK-CK' >
                <label class="form-check-label" for="pilprint2">
                  Printer thermal ukuran 58 inc
                </label>
              </div>
              <div class="row gx-5 mt-3">
                <div class="col">
                  <button type="submit" class="btn btn-sm btn-success form-control"> OK </button>
                </div>
                <div class="col">
                  <button type="button" class="btn btn-sm btn-warning form-control" onclick="document.getElementById('fpilprint').style.display='none'"> Cancel </button>
                </div>
              </div>
              
            </form>  
            <script>
               $('#form-cet').submit(function() {
                  $.ajax({
                      type: 'POST',
                      url: $(this).attr('action'),
                      data: $(this).serialize(),
                      dataType: "json",
                      beforeSend: function(e) {
                        if(e && e.overrideMimeType) {
                          e.overrideMimeType("application/json;charset=UTF-8");
                        }
                      },
                      success: function(response) {
                          $('#viewhapusnota').html(response.hasil);
                      },
                      error: function (xhr, ajaxOptions, thrownError) { // Ketika terjadi error
                        alert(xhr.responseText); // munculkan alert
                      }
                      
                  })
                  return false;
                });
            </script>
          </div>
        </div>
      </div>

      <script>
        function kosongkan(){
          document.getElementById('ketjual').value='-';
          document.getElementById('kd_brg').value='';
          document.getElementById('kd_sat').value='';
          document.getElementById('kd_kat').value='';
          document.getElementById('nm_sat').value='';
          document.getElementById('qty_brg').value='';
          document.getElementById('discitem').value='';
          document.getElementById('no_urutjual').value='';
          // document.getElementById('id_bag').value='';
          document.getElementById('cr_bay').removeAttribute('disabled',true);
          document.getElementById('kd_pel').removeAttribute('disabled',true);
          document.getElementById('no_fakjual').removeAttribute('disabled',true);
          document.getElementById('tgl_fakjual').removeAttribute('disabled',true);
          document.getElementById('tgl_jt').removeAttribute('disabled',true);
          document.getElementById('kd_brg').removeAttribute('disabled',true);        
          document.getElementById('kd_brg').focus();  

        }  
        
        function kosongkan2(){
          document.getElementById('ketjual').value='-';
          document.getElementById('kd_brg').value='';
          document.getElementById('kd_sat').value='';
          document.getElementById('kd_kat').value='';
          document.getElementById('nm_sat').value='';
          document.getElementById('qty_brg').value='';
          document.getElementById('discitem').value='';
          document.getElementById('no_urutjual').value='';
          document.getElementById('tgl_fakjual').value='<?=date('Y-m-d'); ?>';
          document.getElementById('vcari').value='';
          // document.getElementById('id_bag').value='';

          document.getElementById('cr_bay').removeAttribute('disabled',true);
          document.getElementById('kd_pel').removeAttribute('disabled',true);
          document.getElementById('no_fakjual').removeAttribute('disabled',true);
          document.getElementById('tgl_fakjual').removeAttribute('disabled',true);
          document.getElementById('tgl_jt').removeAttribute('disabled',true);
          document.getElementById('kd_brg').removeAttribute('disabled',true);        
          document.getElementById('kd_brg').focus();
          startjual("<?=$kd_toko.';'.$id_user?>");
        }  
      </script>     
    </div><!-- Div.Main -->
  </body>
  <div id="snackbar" style="z-index: 1"></div>   
     <?php
      if(isset($_GET['pesan'])){
        $pesan=$_GET['pesan'];
        if($pesan=="simpan"){
          ?>
            <script>popnew_ok("Data berhasil disimpan");</script>
          <?php
        }else if($pesan=="hapus"){
          ?>
            <script>popnew_warning("Data berhasil dihapus");</script>
          <?php
        }else if($pesan=="gagal"){
          ?>
            <script>popnew_error("Simpan gagal");</script>
          <?php
        }else if($pesan=="hapusupdate"){
          ?>
          <script>popnew_error("Data terupdate, silahkan utk melakukan proses bayar nota kembali");</script>
          <?php
        }else if(substr($pesan,0,8)=="simpanup"){
          $x=explode(';',$pesan);
          $nofak=$x[1];
          ?>
          <script>popnew_warning("Berhasil disimpan");
            alert("Urgent !.. silahkan periksa Piutang Pelanggan No.faktur = "+'<?=$nofak?>');
          </script>
          <?php
        }
      } 
      
      if(!isset($_SESSION['pilprint'])){ ?>
        <script>
        if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)){
        }else{
          document.getElementById('fpilprint').style.display="block";
        }  
        </script> <?php  
      } ?>    
  <script>
  $(document).ready(function(){
    $(".loader1").fadeOut();
  })
  $(window).focus(function(){
    cektokos2();
  });
</script>     
</html>  
    