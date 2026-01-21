<?php
// Direct DB Connection Check
$mysqli = new mysqli("localhost", "root", "", "db_tirtanadi_magang");

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

$result = $mysqli->query("SELECT * FROM laporan");
echo "<h1>Database Check</h1>";
echo "Total Rows in 'laporan' table: " . $result->num_rows . "<br><br>";

if ($result->num_rows > 0) {
    echo "<table border='1'><tr><th>ID</th><th>Tiket</th><th>Lat</th><th>Long</th><th>Kantor Tujuan</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["id"] . "</td>";
        echo "<td>" . $row["nomor_tiket"] . "</td>";
        echo "<td>" . $row["latitude"] . "</td>";
        echo "<td>" . $row["longitude"] . "</td>";
        echo "<td>" . $row["id_kantor_tujuan"] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Table is EMPTY.";
}

$mysqli->close();
?>
