<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
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
    die("Adatbázis hiba: " . $e['message']);
}

$sql = "SELECT id, nev, TO_CHAR(torles_idopont, 'YYYY-MM-DD HH24:MI:SS') AS torolve FROM Kategoria_LOG ORDER BY torles_idopont DESC";
$stmt = oci_parse($conn, $sql);
oci_execute($stmt);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Törölt kategóriák naplója</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="login-form">
        <h2>Törölt kategóriák (napló)</h2>
        <a href="dashboard.php"><button>Vissza</button></a>
        <table>
            <thead>
                <tr><th>ID</th><th>Név</th><th>Törlés ideje</th></tr>
            </thead>
            <tbody>
                <?php while ($row = oci_fetch_assoc($stmt)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['ID']) ?></td>
                        <td><?= htmlspecialchars($row['NEV']) ?></td>
                        <td><?= htmlspecialchars($row['TOROLVE']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>

<?php
oci_free_statement($stmt);
oci_close($conn);
?>