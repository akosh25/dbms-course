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
    $felhasznalo_id = $_SESSION['user_id'];
    $datum = date('Y-m-d'); // vagy jöhet az űrlapról, vagy mostani dátum

    if (empty($cim) || $hossz <= 0) {
        die("Hiányzó vagy érvénytelen adatok.");
    }


    //URL generáláshoz lekérés
    $seq_stmt = oci_parse($conn, "SELECT video_seq.NEXTVAL AS next_id FROM dual");
    oci_execute($seq_stmt);
    $row = oci_fetch_assoc($seq_stmt);
    $video_id = $row['NEXT_ID'];

    $video_url = "video.php?id=" . $video_id;

    $insert_sql = "INSERT INTO Video (
                video_id, cim, leiras, feltoltes_datum, felhasznalo_id, hossz, 
                is_short, video_url)
                VALUES (
                :video_id, :cim, NULL, TO_DATE(:datum, 'YYYY-MM-DD'), :felhasznalo_id, :hossz, 
                NULL, :video_url)";

    $stmt = oci_parse($conn, $insert_sql);
    oci_bind_by_name($stmt, ":video_id", $video_id);
    oci_bind_by_name($stmt, ":cim", $cim);
    oci_bind_by_name($stmt, ":hossz", $hossz);
    oci_bind_by_name($stmt, ":felhasznalo_id", $felhasznalo_id);
    oci_bind_by_name($stmt, ":datum", $datum);
    oci_bind_by_name($stmt, ":video_url", $video_url);
    $result = oci_execute($stmt,OCI_COMMIT_ON_SUCCESS);

    if ($result) {
        // Sikeres beszúrás, átirányítás vagy üzenet
        echo "sikeres";
        header("Location: videok.php?uzenet=Videó sikeresen hozzáadva!");
        exit();
    } else {
        $e = oci_error($stmt);
        die("Hiba a beszúrás során: " . $e['message']);
    }
}

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <title>Új videó feltöltése</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <?php include 'menu.php'; ?>

  <h1 style="margin-top: 80px;">Új videó feltöltése</h1>

  <form method="POST" action="uj_video_beszur.php" style="max-width: 400px; margin: 0 auto;">
    <div style="margin-bottom: 10px;">
      <label for="cim">Cím:</label><br>
      <input type="text" id="cim" name="cim" required style="width: 100%;">
    </div>
    <div style="margin-bottom: 10px;">
      <label for="hossz">Hossz (másodperc):</label><br>
      <input type="number" id="hossz" name="hossz" min="1" required style="width: 100%;">
    </div>
    <div style="margin-bottom: 10px;">
      <label for="datum">Feltöltés dátuma:</label><br>
      <input type="date" id="datum" name="datum" value="<?php echo date('Y-m-d'); ?>" style="width: 100%;">
    </div>
    <button type="submit" style="padding: 8px 16px;">Feltöltés</button>
  </form>
</body>
</html>