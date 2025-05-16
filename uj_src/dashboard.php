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

// Lejátszási listák lekérdezése
$felhasznalo_id = $_SESSION['user_id'];
$lejátszási_listák = [];

$list_stmt = oci_parse($conn, "
    SELECT lista_id, nev, TO_CHAR(letrehozas_datum, 'YYYY-MM-DD HH24:MI') AS datum
    FROM LejatszasiLista
    WHERE felhasznalo_id = :felhasznalo_id
    ORDER BY letrehozas_datum DESC
");
oci_bind_by_name($list_stmt, ":felhasznalo_id", $felhasznalo_id);
oci_execute($list_stmt);
while ($row = oci_fetch_assoc($list_stmt)) {
    $lejátszási_listák[] = $row;
}
oci_free_statement($list_stmt);
oci_close($conn);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Videó Megosztó Platform</title>
    <link rel="stylesheet" href="style.css">

</head>
<body>
<?php include 'menu.php'; ?>


<div class="container">
    <div class="login-form">
        <h2>Üdvözöllek, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <p>Email címed: <?php echo htmlspecialchars($_SESSION['email']); ?></p>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div style="margin: 20px 0;">
                <a href="kategoria_form.php" style="text-decoration: none;">
                    <button>Új kategória hozzáadása</button>
                </a>
                <a href="kategoria_lista.php" style="text-decoration: none; margin-top: 10px;">
                    <button>Kategóriák kezelése</button>
                </a>
                <a href="kategoria_log.php" style="text-decoration: none; margin-top: 10px;">
                    <button>Törölt kategóriák naplója</button>
                </a>
            </div>
        <?php endif; ?>

         <h3>Lejátszási listáid:</h3>

        <?php if (count($lejátszási_listák) === 0): ?>
            <p>Nincs saját lejátszási listád.</p>
        <?php else: ?>
            <ul style="list-style: none; padding: 0;">
    <?php foreach ($lejátszási_listák as $lista): ?>
        <li style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1px solid #ccc; padding: 5px 0;">
            <div>
                <strong><?php echo htmlspecialchars($lista['NEV']); ?></strong><br>
                <small>Létrehozva: <?php echo htmlspecialchars($lista['DATUM']); ?></small>
            </div>
            <form action="torol_lejatszasi_lista.php" method="post" onsubmit="return confirm('Biztosan törölni szeretnéd ezt a listát?');" style="margin-left: 20px;">
                <input type="hidden" name="lista_id" value="<?php echo (int)$lista['LISTA_ID']; ?>">
                <button type="submit">Törlés</button>
            </form>
        </li>
    <?php endforeach; ?>
</ul>

        <?php endif; ?>

        <form action="logout.php" method="post">
            <div class="form-group">
                <button type="submit">Kilépés</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>