<?php
session_start();

// 1. Ellenőrzések
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("❌ Hibás kérés: csak POST metódus engedélyezett.");
}

if (!isset($_SESSION['user_id'])) {
    die("❌ Hiba: csak bejelentkezett felhasználó adhat kedvenchez videót.");
}

if (!isset($_POST['video_id']) || !is_numeric($_POST['video_id'])) {
    die("❌ Hiba: hiányzó vagy érvénytelen videó azonosító.");
}

$video_id = (int) $_POST['video_id'];
$felhasznalo_id = $_SESSION['user_id'];
$datum = date('Y-m-d');

$host = "localhost";
$port = "1521";
$sid = "xe";
$username = "LOGIN";
$password = "oracle";

$conn_string = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=$host)(PORT=$port))(CONNECT_DATA=(SID=$sid)))";
$conn = oci_connect($username, $password, $conn_string, 'AL32UTF8');

if (!$conn) {
    $e = oci_error();
    die("❌ Adatbázis-kapcsolat hiba: " . $e['message']);
}

$insert_sql = "INSERT INTO Kedvencek (
            felhasznalo_id, video_id, hozzaadas_datum)
            VALUES (
            :felhasznalo_id, :video_id, TO_DATE(:datum, 'YYYY-MM-DD'))";

$stmt = oci_parse($conn, $insert_sql);
oci_bind_by_name($stmt, ":felhasznalo_id", $felhasznalo_id);
oci_bind_by_name($stmt, ":video_id", $video_id);
oci_bind_by_name($stmt, ":datum", $datum);

$result = oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);


oci_free_statement($stmt);
oci_close($conn);

if ($result) {
    header("Location: videok.php?uzenet=Videó+sikeresen+hozzáadva+a+kedvencekhez");
    exit();
} else {
    $e = oci_error($stmt);
    echo "<p style='color:red;'><strong>❌ Hiba a kedvenc hozzáadásakor:</strong><br>" . htmlspecialchars($e['message']) . "</p>";
}
?>
