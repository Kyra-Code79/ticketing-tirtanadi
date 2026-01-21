<?php
// force_types_migration.php
$db = new mysqli('localhost', 'root', '', 'db_tirtanadi_magang');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// 1. Create master_types
$sql1 = "CREATE TABLE IF NOT EXISTS master_types (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_tipe VARCHAR(100) NOT NULL,
    warna VARCHAR(10) DEFAULT '#6c757d'
)";
if ($db->query($sql1) === TRUE) {
    echo "Table master_types created.\n";
} else {
    echo "Error creating table: " . $db->error . "\n";
}

// 2. Add type_id to master_kantor
// Check if exists first
$check = $db->query("SHOW COLUMNS FROM master_kantor LIKE 'type_id'");
if ($check->num_rows == 0) {
    $sql2 = "ALTER TABLE master_kantor ADD COLUMN type_id INT(11) UNSIGNED NULL AFTER nama_kantor";
    if ($db->query($sql2) === TRUE) {
        echo "Added type_id column.\n";
    }
    
    // FK
    $db->query("ALTER TABLE master_kantor ADD CONSTRAINT fk_type_id FOREIGN KEY (type_id) REFERENCES master_types(id) ON DELETE SET NULL");
}

// 3. Seed Defaults
$defaults = [
    ['Pusat', '#dc3545'],
    ['Pemasaran', '#0d6efd'],
    ['Produksi/IPAM', '#198754']
];

foreach ($defaults as $def) {
    $name = $def[0];
    $color = $def[1];
    
    // Check if exists
    $res = $db->query("SELECT id FROM master_types WHERE nama_tipe = '$name'");
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $newId = $row['id'];
    } else {
        $db->query("INSERT INTO master_types (nama_tipe, warna) VALUES ('$name', '$color')");
        $newId = $db->insert_id;
    }
    
    // Update Master Kantor
    // We assume 'tipe' column still exists and has values
    $db->query("UPDATE master_kantor SET type_id = $newId WHERE tipe = '$name'");
}

echo "Data migrated.\n";

// 4. Drop old column
// Optional: Check if column exists
$check2 = $db->query("SHOW COLUMNS FROM master_kantor LIKE 'tipe'");
if ($check2->num_rows > 0) {
    $db->query("ALTER TABLE master_kantor DROP COLUMN tipe");
    echo "Dropped old 'tipe' column.\n";
}

$db->close();
?>
