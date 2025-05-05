<?php
session_start();

// csak admin
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

// törlés esetén
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $sql = "DELETE FROM Kategoria WHERE kategoria_id = :id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":id", $delete_id);
    oci_execute($stmt);
}

// kategóriák lekérdezése
$sql = "SELECT kategoria_id, nev, leiras FROM Kategoria ORDER BY nev";
$stmt = oci_parse($conn, $sql);
oci_execute($stmt);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Kategóriák listája</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="login-form">
        <h2>Kategóriák</h2>
        <a href="dashboard.php"><button>Vissza a főoldalra</button></a>
        <table style="width: 100%; margin-top: 20px;">
            <thead>
                <tr>
                    <th>Név</th>
                    <th>Leírás</th>
                    <th>Művelet</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = oci_fetch_assoc($stmt)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['NEV']); ?></td>
                        <td><?php echo htmlspecialchars($row['LEIRAS']); ?></td>
                        <td style="display: flex; gap: 5px;">
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?php echo $row['KATEGORIA_ID']; ?>">
                                <button type="submit" onclick="return confirm('Biztosan törölni szeretnéd?');">Törlés</button>
                            </form>
                            <a href="kategoria_szerkesztes.php?id=<?php echo $row['KATEGORIA_ID']; ?>">
                                <button type="button">Szerkesztés</button>
                            </a>
                        </td>
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