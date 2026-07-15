<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
include 'config.php';

$cones   = opendtcek();
$no_fakjual = $tgl_jual = $kd_toko = $nm_pel = $alamat = $tgltime = '';
$disctot = $voucher = $ongkir = 0;
$kd_bayar = $bayar = $susuk = $saldohut = $jtempo = '';
$items = array();

if(isset($_GET['dts'])){
  $xd=explode(';',$_GET['dts']);
  $no_fakjual = isset($xd[0]) ? $xd[0] : '';
  $tgl_jual   = isset($xd[1]) ? $xd[1] : '';
  $kd_toko    = isset($xd[2]) ? $xd[2] : '';
  $nm_pel     = isset($xd[3]) ? $xd[3] : '';
  $alamat     = isset($xd[4]) ? $xd[4] : '';
  $tgltime    = isset($xd[5]) ? $xd[5] : '';
  $disctot    = isset($xd[6]) ? floatval($xd[6]) : 0;
  $voucher    = isset($xd[7]) ? floatval($xd[7]) : 0;
  $ongkir     = isset($xd[8]) ? floatval($xd[8]) : 0;
  $kd_bayar   = isset($xd[9]) ? $xd[9] : '';
  $bayar      = isset($xd[10]) ? $xd[10] : '';
  $susuk      = isset($xd[11]) ? $xd[11] : '';
  $saldohut   = isset($xd[12]) ? $xd[12] : '';
  $jtempo     = isset($xd[13]) ? $xd[13] : '';
}

$no_fakjual = mysqli_real_escape_string($cones, $no_fakjual);
$tgl_jual   = mysqli_real_escape_string($cones, $tgl_jual);
$kd_toko    = mysqli_real_escape_string($cones, $kd_toko);

// Ambil data member dari transaksi
$kd_member = '';
$nm_member = '';
$poin_earned = 0;
$poin_saldo = 0;
$cek_member = mysqli_query($cones, "SELECT kd_member, poin_earned FROM mas_jual WHERE no_fakjual='$no_fakjual' AND tgl_jual='$tgl_jual' AND kd_toko='$kd_toko' LIMIT 1");
if ($cek_member && mysqli_num_rows($cek_member) > 0) {
  $dt_member = mysqli_fetch_assoc($cek_member);
  $kd_member = isset($dt_member['kd_member']) ? trim($dt_member['kd_member']) : '';
  $poin_earned = isset($dt_member['poin_earned']) ? floatval($dt_member['poin_earned']) : 0;
  if (!empty($kd_member)) {
    $kd_member_esc = mysqli_real_escape_string($cones, $kd_member);
    $sqlmember = mysqli_query($cones, "SELECT nm_member, poin FROM member WHERE kd_member='$kd_member_esc' AND kd_toko='$kd_toko' LIMIT 1");
    if ($sqlmember && mysqli_num_rows($sqlmember) > 0) {
      $datamember = mysqli_fetch_assoc($sqlmember);
      $nm_member = isset($datamember['nm_member']) ? trim($datamember['nm_member']) : '';
      $poin_saldo = isset($datamember['poin']) ? floatval($datamember['poin']) : 0;
    }
    if ($sqlmember) {
      mysqli_free_result($sqlmember);
    }
  }
}
if ($cek_member) {
  mysqli_free_result($cek_member);
}

$sql=mysqli_query($cones,"SELECT *,sum(dum_jual.qty_brg) AS qty_brg FROM dum_jual LEFT JOIN kemas ON dum_jual.kd_sat=kemas.no_urut WHERE dum_jual.no_fakjual='$no_fakjual' AND dum_jual.tgl_jual='$tgl_jual' AND dum_jual.kd_toko='$kd_toko' GROUP BY dum_jual.kd_brg,dum_jual.kd_sat,dum_jual.discrp,dum_jual.hrg_jual ORDER BY dum_jual.no_urut ASC");
$subtot=$total=$gdisc1=0;
if($sql && mysqli_num_rows($sql)>0){
    while($data=mysqli_fetch_assoc($sql)){
      $nm_sat=' '.ucwords(strtolower($data['nm_sat1']));
      $hrg_jual=round($data['hrg_jual'],0);
            
      if ($data['discrp']>0){
        $subtot=($data['hrg_jual']-$data['discrp'])*$data['qty_brg'];
      }else{
        $subtot=$data['hrg_jual']*$data['qty_brg'];
      }

      $qty_brg=round($data['qty_brg'],0);   
      $discrp=round($data['discrp'],0);
      $hrg_jual=round($data['hrg_jual'],0);
      $total=$total+round($subtot,0); 
      $total=round($total,0);
      $subtot=round($subtot,0);
      
      $items[] = [
        "nmbrg" => trim(ucwords(strtolower($data["nm_brg"]))),
        "qty"   => $qty_brg,
        "sat"   => $nm_sat,
        "hrg"   => $hrg_jual,
        "disc"  => $discrp,
        "subtot"=> $subtot
      ];
    }
}
mysqli_close($cones);

$nm_pel_asli = trim($nm_pel);
$has_member = (!empty($kd_member) && !empty($nm_member));

// Print bridge mencetak field terpisah: nm_pel, nm_member, poin_saldo — tanpa Alamat
$totbelanja=($total-($disctot+$voucher))+$ongkir;
$disctot_fmt=gantiti($disctot);$voucher_fmt=gantiti($voucher);$ongkir_fmt=gantiti($ongkir);
$output = [
  "no_fakjual"  => $no_fakjual,
  "nm_pel"      => $nm_pel_asli,
  "nm_pel_asli" => $nm_pel_asli,
  // kosong + flag: bridge tidak boleh cetak label Alamat
  "alamat"      => "",
  "show_alamat" => 0,
  "tgltime"     => $tgltime,
  "tgl_jual"    => $tgl_jual,
  "belanja"     => $total,
  "total"       => gantiti($totbelanja),
  "disctot"     => $disctot_fmt,
  "voucher"     => $voucher_fmt,
  "ongkir"      => $ongkir_fmt,
  "disctot_raw" => $disctot,
  "voucher_raw" => $voucher,
  "ongkir_raw"  => $ongkir,
  "kd_bayar"    => $kd_bayar,
  "bayar"       => $bayar,
  "susuk"       => $susuk,
  "saldohut"    => $saldohut,
  "jtempo"      => $jtempo,
  "kd_member"   => $kd_member,
  "nm_member"   => $has_member ? $nm_member : '',
  "member"      => $has_member ? $nm_member : '',
  "poin_earned" => $poin_earned,
  "poin_dapat"  => $poin_earned,
  "poin_saldo"  => $poin_saldo,
  "poin"        => $poin_saldo,
  "has_member"  => $has_member ? 1 : 0,
  "items"       => $items
];
echo json_encode([
    "success" => true,
    "data" => $output
], JSON_UNESCAPED_UNICODE);
