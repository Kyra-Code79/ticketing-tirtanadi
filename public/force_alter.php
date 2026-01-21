<?php
// force_alter.php
$db = new mysqli('localhost', 'root', '', 'db_tirtanadi_magang');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// 1. Add email to laporan
$sql1 = "ALTER TABLE laporan ADD COLUMN email VARCHAR(100) NULL AFTER no_hp";
if ($db->query($sql1) === TRUE) {
    echo "Added email column successfully.\n";
} else {
    echo "Error adding email: " . $db->error . "\n";
}

// 2. Add latitude/longitude to master_kantor
$sql2 = "ALTER TABLE master_kantor ADD COLUMN latitude DECIMAL(10,8) NULL AFTER alamat_kantor";
if ($db->query($sql2) === TRUE) {
    echo "Added latitude column successfully.\n";
} else {
    echo "Error adding latitude: " . $db->error . "\n";
}
$sql3 = "ALTER TABLE master_kantor ADD COLUMN longitude DECIMAL(11,8) NULL AFTER latitude";
if ($db->query($sql3) === TRUE) {
    echo "Added longitude column successfully.\n";
} else {
    echo "Error adding longitude: " . $db->error . "\n";
}

// 3. Add detail_aduan to laporan
$sql4 = "ALTER TABLE laporan ADD COLUMN detail_aduan TEXT NULL AFTER isi_laporan";
if ($db->query($sql4) === TRUE) {
    echo "Added detail_aduan column successfully.\n";
} else {
    echo "Error adding detail_aduan: " . $db->error . "\n";
}

$db->close();
?>
