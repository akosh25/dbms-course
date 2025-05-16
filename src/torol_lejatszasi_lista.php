<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_POST['lista_id'])) {
    header("Location: dashboard.php");
    exit();
}

$lista_id = $_POST['lista_id'];
$felhasznalo_id = $_SESSION['user_id'];

// Adatbáziskapcsolat
$host = "localhost";
$port = "1521";
$sid = "xe";
$username = "LOGIN";
$password = "oracle";
$conn_string = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=$host)(PORT=$port))(CONNECT_DATA=(SID=$sid)))";
$conn = oci_connect($username, $password, $conn_string, 'AL32UTF8');

if (!$conn) {
    $e = oci_error();
    die("Kapcsolódási hiba: " . $e['message']);
}

// 1. Töröljük a lista videóit
$delete_videok = oci_parse($conn, "DELETE FROM LejatszasiListaVideo WHERE lista_id = :lista_id");
oci_bind_by_name($delete_videok, ":lista_id", $lista_id);
oci_execute($delete_videok);
oci_free_statement($delete_videok);

// 2. Töröljük a listát 
$delete_lista = oci_parse($conn, "
    DELETE FROM LejatszasiLista
    WHERE lista_id = :lista_id AND felhasznalo_id = :felhasznalo_id
");
oci_bind_by_name($delete_lista, ":lista_id", $lista_id);
oci_bind_by_name($delete_lista, ":felhasznalo_id", $felhasznalo_id);
oci_execute($delete_lista);
oci_free_statement($delete_lista);

oci_close($conn);
header("Location: dashboard.php?uzenet=" . urlencode("A lejátszási lista törlésre került."));
exit();
?>
