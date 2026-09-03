<?php
if (!function_exists('ensureCrewTable')) {
  function ensureCrewTable($connect) {
    if (!$connect) {
      return;
    }
    mysqli_query($connect, "CREATE TABLE IF NOT EXISTS crew (
      no_urut INT NOT NULL AUTO_INCREMENT,
      kd_crew VARCHAR(50) NOT NULL,
      nm_crew VARCHAR(255) NOT NULL,
      nm_toko VARCHAR(255) DEFAULT NULL,
      al_crew VARCHAR(255) DEFAULT '',
      no_telp VARCHAR(50) DEFAULT '',
      aktif TINYINT(1) NOT NULL DEFAULT 1,
      kd_toko VARCHAR(50) DEFAULT NULL,
      PRIMARY KEY (no_urut),
      KEY idx_kd_crew (kd_crew),
      KEY idx_crew_toko (kd_toko)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $cek_aktif = mysqli_query($connect, "SHOW COLUMNS FROM crew LIKE 'aktif'");
    if ($cek_aktif && mysqli_num_rows($cek_aktif) == 0) {
      mysqli_query($connect, "ALTER TABLE crew ADD COLUMN aktif TINYINT(1) NOT NULL DEFAULT 1");
    }
    if ($cek_aktif) {
      mysqli_free_result($cek_aktif);
    }
  }
}

if (!function_exists('ensureMasJualCrewColumn')) {
  function ensureMasJualCrewColumn($connect) {
    if (!$connect) {
      return;
    }
    $cek = mysqli_query($connect, "SHOW COLUMNS FROM mas_jual LIKE 'kd_crew'");
    if ($cek && mysqli_num_rows($cek) == 0) {
      mysqli_query($connect, "ALTER TABLE mas_jual ADD COLUMN kd_crew VARCHAR(50) DEFAULT '' AFTER kd_member");
    }
    if ($cek) {
      mysqli_free_result($cek);
    }
  }
}
