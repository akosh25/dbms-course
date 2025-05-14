<?php
session_start();

// Csak admin érheti el
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Ellenőrizzük, hogy kaptunk-e kategória ID-t
if (!isset($_GET['id'])) {
    header("Location: kategoria_lista.php");
    exit();
}

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
    die("Adatbázis hiba: " . $e['message']);
}

// Inicializáljuk az üzeneteket
$success_message = "";
$error_message = "";

// Aktuális kategória adatainak lekérése
$kategoria_id = $_GET['id'];

$sql = "SELECT nev, leiras FROM Kategoria WHERE kategoria_id = :id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ":id", $kategoria_id);
oci_execute($stmt);
$kategoria = oci_fetch_assoc($stmt);

if (!$kategoria) {
    // Ha nincs ilyen kategória
    oci_free_statement($stmt);
    oci_close($conn);
    header("Location: kategoria_lista.php");
    exit();
}

oci_free_statement($stmt);

// Ha módosítják az adatokat
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nev = trim($_POST["nev"] ?? "");
    $leiras = trim($_POST["leiras"] ?? "");

    if (empty($nev)) {
        $error_message = "A kategórianév nem lehet üres.";
    } else {
        $update_sql = "UPDATE Kategoria SET nev = :nev, leiras = :leiras WHERE kategoria_id = :id";
        $update_stmt = oci_parse($conn, $update_sql);
        oci_bind_by_name($update_stmt, ":nev", $nev);
        oci_bind_by_name($update_stmt, ":leiras", $leiras);
        oci_bind_by_name($update_stmt, ":id", $kategoria_id);

        if (oci_execute($update_stmt)) {
            $success_message = "A kategória sikeresen frissítve.";
            // Frissítsük a megjelenített adatokat is
            $kategoria['NEV'] = $nev;
            $kategoria['LEIRAS'] = $leiras;
        } else {
            $e = oci_error($update_stmt);
            $error_message = "Hiba történt: " . $e['message'];
        }

        oci_free_statement($update_stmt);
    }
}

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Kategória szerkesztése</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="login-form">
        <h2>Kategória szerkesztése</h2>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="nev">Kategória neve:</label>
                <input type="text" id="nev" name="nev" required value="<?php echo htmlspecialchars($kategoria['NEV']); ?>">
            </div>

            <div class="form-group">
                <label for="leiras">Leírás:</label>
                <input type="text" id="leiras" name="leiras" value="<?php echo htmlspecialchars($kategoria['LEIRAS']); ?>">
            </div>

            <div class="form-group">
                <button type="submit">Mentés</button>
                <a href="kategoria_lista.php"><button type="button">Mégse</button></a>
            </div>
        </form>
    </div>
</div>
</body>
</html>