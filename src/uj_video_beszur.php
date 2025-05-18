<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$host = "localhost";
$port = "1521";
$sid = "xe";
$username = "LOGIN";
$password = "oracle";
$conn_string = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=$host)(PORT=$port))(CONNECT_DATA=(SID=$sid)))";
$conn = oci_connect($username, $password, $conn_string, 'AL32UTF8');

if (!$conn) {
    $e = oci_error();
    die("Adatbázis-kapcsolat sikertelen: " . $e['message']);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $cim = $_POST['cim'] ?? '';
    $hossz = (int)($_POST['hossz'] ?? 0);
    $feltolto_id = $_SESSION['user_id'];
    $datum = date('Y-m-d'); // vagy jöhet az űrlapról, vagy mostani dátum

    if (empty($cim) || $hossz <= 0) {
        die("Hiányzó vagy érvénytelen adatok.");
    }

    // Előkészítés és végrehajtás
    $stmt = oci_parse($conn, "BEGIN uj_video_beszur(:cim, :hossz, :feltolto_id, :datum); END;");
    oci_bind_by_name($stmt, ":cim", $cim);
    oci_bind_by_name($stmt, ":hossz", $hossz);
    oci_bind_by_name($stmt, ":feltolto_id", $feltolto_id);
    oci_bind_by_name($stmt, ":datum", $datum);
    $result = oci_execute($stmt);

    if ($result) {
        // Sikeres beszúrás, átirányítás vagy üzenet
        header("Location: videok.php?uzenet=Videó sikeresen hozzáadva");
        exit();
    } else {
        $e = oci_error($stmt);
        die("Hiba a beszúrás során: " . $e['message']);
    }
}

oci_close($conn);
?>