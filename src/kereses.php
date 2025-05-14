<?php
session_start();

// Szerepkör meghatározása
$szerepkor = $_SESSION['szerepkor'] ?? 'guest';

$host = "localhost";
$port = "1521";
$sid = "xe"; 
$username = "LOGIN";
$password = "oracle";

// Kapcsolódás Oracle adatbázishoz
$conn_string = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=$host)(PORT=$port))(CONNECT_DATA=(SID=$sid)))";
$conn = oci_connect($username, $password, $conn_string, 'AL32UTF8');

if (!$conn) {
    $e = oci_error();
    die("Kapcsolódási hiba: " . $e['message']);
}

// Kategóriák lekérése dropdownhoz
$kategoriak = [];
$kategoria_stmt = oci_parse($conn, "SELECT kategoria_id, nev FROM Kategoria ORDER BY nev");
oci_execute($kategoria_stmt);
while ($row = oci_fetch_assoc($kategoria_stmt)) {
    $kategoriak[] = $row;
}

// Keresés feldolgozása
$where = [];
$params = [];

if (!empty($_GET['kulcsszo'])) {
    $where[] = "(LOWER(v.cim) LIKE :kulcsszo OR LOWER(v.leiras) LIKE :kulcsszo)";
    $kulcsszo = '%' . strtolower($_GET['kulcsszo']) . '%';
}

if (!empty($_GET['kategoria'])) {
    $where[] = "v.kategoria_id = :kategoria";
    $kategoria = $_GET['kategoria'];
}

if ($szerepkor === 'admin') {
    if (!empty($_GET['jelentett'])) {
        $where[] = "v.is_reported = 1"; // csak példa, nincs ilyen oszlop alapból
    }
    if (!empty($_GET['szabalyellenes'])) {
        $where[] = "v.is_illegal = 1"; // csak példa, nincs ilyen oszlop alapból
    }
}

$where_clause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT v.cim, v.leiras, v.video_url FROM Video v $where_clause";

$stmt = oci_parse($conn, $sql);

if (isset($kulcsszo)) {
    oci_bind_by_name($stmt, ":kulcsszo", $kulcsszo);
}
if (isset($kategoria)) {
    oci_bind_by_name($stmt, ":kategoria", $kategoria);
}

oci_execute($stmt);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Videó keresés</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Videó keresés</h2>

    <form method="get" action="kereses.php">
        <input type="text" name="kulcsszo" placeholder="Kulcsszó" value="<?php echo htmlspecialchars($_GET['kulcsszo'] ?? '') ?>">

        <select name="kategoria">
            <option value="">-- Kategória --</option>
            <?php foreach ($kategoriak as $kat): ?>
                <option value="<?php echo $kat['KATEGORIA_ID']; ?>" <?php echo (isset($_GET['kategoria']) && $_GET['kategoria'] == $kat['KATEGORIA_ID']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($kat['NEV']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php if ($szerepkor === 'admin'): ?>
            <label><input type="checkbox" name="jelentett" <?php echo isset($_GET['jelentett']) ? 'checked' : ''; ?>> Jelentett</label>
            <label><input type="checkbox" name="szabalyellenes" <?php echo isset($_GET['szabalyellenes']) ? 'checked' : ''; ?>> Szabálytalan</label>
        <?php endif; ?>

        <button type="submit">Keresés</button>
    </form>

    <h3>Találatok:</h3>
    <ul>
        <?php while ($video = oci_fetch_assoc($stmt)): ?>
            <li>
                <strong><?php echo htmlspecialchars($video['CIM']); ?></strong><br>
                <?php echo htmlspecialchars($video['LEIRAS']); ?><br>
                <a href="<?php echo htmlspecialchars($video['VIDEO_URL']); ?>" target="_blank">Megtekintés</a>
            </li>
        <?php endwhile; ?>
    </ul>
</body>
</html>
