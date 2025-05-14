<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ellenőrizzük, hogy jött-e kategória ID POST-ban
if (!isset($_POST['kategoria_id']) || empty($_POST['kategoria_id'])) {
    die("Nincs megadva kategória.");
}

$kategoria_id = $_POST['kategoria_id'];
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
    die("Adatbázis-kapcsolat sikertelen: " . $e['message']);
}

    // Új lista ID lekérése szekvenciából
    $seq_stmt = oci_parse($conn, "SELECT lejatszasilista_seq.NEXTVAL AS uj_id FROM dual");
    oci_execute($seq_stmt);
    $seq_row = oci_fetch_assoc($seq_stmt);
    $uj_lista_id = $seq_row['UJ_ID'];
    oci_free_statement($seq_stmt);

// Lekérdezzük, hány videó van az adott kategóriában
$count_stmt = oci_parse($conn, "
    SELECT COUNT(v.video_id) AS video_darab
    FROM Video v
    JOIN Kategoria k ON v.kategoria_id = k.kategoria_id
    WHERE k.kategoria_id = :kategoria_id
");
oci_bind_by_name($count_stmt, ":kategoria_id", $kategoria_id);
oci_execute($count_stmt);
$count_row = oci_fetch_assoc($count_stmt);
$video_darab = $count_row['VIDEO_DARAB'];
oci_free_statement($count_stmt);

// 1. Lejatszasi lista beszúrása az adott kategoria nevével
$sql_insert_lista = "
    INSERT INTO LejatszasiLista (lista_id, felhasznalo_id, nev, publikus, letrehozas_datum)
    SELECT :lista_id, :felhasznalo_id, k.nev, 0, SYSTIMESTAMP
    FROM Kategoria k
    JOIN Felhasznalo f ON f.felhasznalo_id = :felhasznalo_id
    WHERE k.kategoria_id = :kategoria_id
";
$lista_stmt = oci_parse($conn, $sql_insert_lista);
oci_bind_by_name($lista_stmt, ":lista_id", $uj_lista_id);
oci_bind_by_name($lista_stmt, ":felhasznalo_id", $felhasznalo_id);
oci_bind_by_name($lista_stmt, ":kategoria_id", $kategoria_id);

if (!oci_execute($lista_stmt)) {
    $e = oci_error($lista_stmt);
    die("Nem sikerült a lejátszási lista létrehozása: " . $e['message']);
}
oci_free_statement($lista_stmt);

// 2. Videók lekérdezése a kategória alapján
$sql_videok = "
    SELECT video_id FROM Video WHERE kategoria_id = :kategoria_id
";
$video_stmt = oci_parse($conn, $sql_videok);
oci_bind_by_name($video_stmt, ":kategoria_id", $kategoria_id);
oci_execute($video_stmt);

$beszur_stmt = oci_parse($conn, "
    INSERT INTO LejatszasiListaVideo (lista_id, video_id)
    VALUES (:lista_id, :video_id)
");

$pozicio = 1;
while ($row = oci_fetch_assoc($video_stmt)) {
    $video_id = $row['VIDEO_ID'];




    $kapcs_stmt = oci_parse($conn, "
        INSERT INTO LejatszasiListaVideo (lista_id, video_id, pozicio, hozzaadas_datum)
        VALUES (:lista_id, :video_id, :pozicio, SYSTIMESTAMP)
    ");
    oci_bind_by_name($kapcs_stmt, ":lista_id", $uj_lista_id);
    oci_bind_by_name($kapcs_stmt, ":video_id", $video_id);
    oci_bind_by_name($kapcs_stmt, ":pozicio", $pozicio);
    oci_execute($kapcs_stmt);
    oci_free_statement($kapcs_stmt);

    $pozicio++;
}
oci_free_statement($video_stmt);
oci_free_statement($beszur_stmt);
oci_close($conn);

header("Location: videok.php?uzenet=" . urlencode("$video_darab videó került a lejátszási listádba a kiválasztott kategóriában."));

exit();
?>
