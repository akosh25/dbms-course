<?php
session_start();

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

// Kategóriák lekérdezése
$kategoriak = [];
$kategoria_stmt = oci_parse($conn, "SELECT kategoria_id, nev FROM Kategoria ORDER BY nev");
oci_execute($kategoria_stmt);
while ($row = oci_fetch_assoc($kategoria_stmt)) {
    $kategoriak[] = $row;
}

// Keresési feltételek
$kulcsszo = $_GET['kulcsszo'] ?? '';
$kategoria_id = $_GET['kategoria'] ?? '';
$csak_kedvencek = isset($_GET['csak_kedvencek']) && isset($_SESSION['user_id']);

// Alap SQL
$sql = "
    SELECT v.video_id, v.cim, v.leiras, k.nev AS kategoria_nev, v.video_url
    FROM Video v
    LEFT JOIN VideoKategoria vk ON v.video_id = vk.video_id
    LEFT JOIN Kategoria k ON vk.kategoria_id = k.kategoria_id
";
if ($csak_kedvencek) {
    $sql .= " JOIN Kedvencek f ON v.video_id = f.video_id AND f.felhasznalo_id = :felhasznalo_id";
}
$sql .= " WHERE 1=1";

if (!empty($kulcsszo)) {
    $sql .= " AND (LOWER(v.cim) LIKE '%' || LOWER(:kulcsszo) || '%' OR LOWER(v.leiras) LIKE '%' || LOWER(:kulcsszo) || '%')";
}
if (!empty($kategoria_id)) {
    $sql .= " AND EXISTS (
        SELECT 1 FROM VideoKategoria vk2 
        WHERE vk2.video_id = v.video_id 
        AND vk2.kategoria_id = :kategoria_id
    )";
}
$sql .= " ORDER BY v.feltoltes_datum DESC";

// Lekérdezés előkészítése
$stmt = oci_parse($conn, $sql);
if ($csak_kedvencek) {
    oci_bind_by_name($stmt, ":felhasznalo_id", $_SESSION['user_id']);
}
if (!empty($kulcsszo)) {
    oci_bind_by_name($stmt, ":kulcsszo", $kulcsszo);
}
if (!empty($kategoria_id)) {
    oci_bind_by_name($stmt, ":kategoria_id", $kategoria_id);
}
oci_execute($stmt);

// Eredmények lekéréde
$talalatok = [];
while ($row = oci_fetch_assoc($stmt)) {
    $talalatok[] = $row;
}
oci_free_statement($stmt);

// Legújabb 5 videó lekérdezése
$uj_videok = [];
$uj_stmt = oci_parse($conn, "
    SELECT *
    FROM (
        SELECT v.video_id,
               v.cim,
               v.video_url,
               TO_CHAR(v.feltoltes_datum, 'YYYY-MM-DD') AS feltoltes_datum,
               k.nev AS kategoria_nev,
               COUNT(f.felhasznalo_id) AS kedvenc_db
        FROM Video v
        LEFT JOIN VideoKategoria vk ON v.video_id = vk.video_id
        LEFT JOIN Kategoria k ON vk.kategoria_id = k.kategoria_id
        LEFT JOIN Kedvencek f ON v.video_id = f.video_id
        GROUP BY v.video_id, v.cim, v.video_url, v.feltoltes_datum, k.nev
        ORDER BY v.feltoltes_datum DESC
    )
    WHERE ROWNUM <= 5
");
oci_execute($uj_stmt);
while ($row = oci_fetch_assoc($uj_stmt)) {
    $uj_videok[] = $row;
}
oci_free_statement($uj_stmt);

// Kategóriánkénti videók számának lekérdezése
$statisztika = [];
$stat_stmt = oci_parse($conn, "
    SELECT k.nev AS kategoria_nev, COUNT(v.video_id) AS video_db
    FROM Video v
    JOIN VideoKategoria vk ON v.video_id = vk.video_id
    JOIN Kategoria k ON vk.kategoria_id = k.kategoria_id
    GROUP BY k.nev
    ORDER BY video_db DESC
");
oci_execute($stat_stmt);
while ($row = oci_fetch_assoc($stat_stmt)) {
    $statisztika[] = $row;
}
oci_free_statement($stat_stmt);

// Legnézettebb 5 videó lekérdezése (összetett: JOIN + GROUP BY + COUNT)
$nezett_videok = [];
$nezett_stmt = oci_parse($conn, "
    SELECT
        v.video_id,
        v.cim,
        COALESCE((
            SELECT LISTAGG(k.nev, ', ') WITHIN GROUP (ORDER BY k.nev)
            FROM VideoKategoria vk
            JOIN Kategoria k ON vk.kategoria_id = k.kategoria_id
            WHERE vk.video_id = v.video_id
        ), 'Nincs kategória') AS kategoriak,
        COUNT(m.megtekintes_id) AS megtekintes_szam
    FROM Video v
    JOIN Megtekintes m ON v.video_id = m.video_id
    GROUP BY v.video_id, v.cim
    ORDER BY megtekintes_szam DESC
    FETCH FIRST 5 ROWS ONLY
");
oci_execute($nezett_stmt);
while ($row = oci_fetch_assoc($nezett_stmt)) {
    $nezett_videok[] = $row;
}
oci_free_statement($nezett_stmt);

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Videók keresése</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'menu.php'; ?>

    <h1 style="margin-top: 80px;">Videók keresése</h1>


    <?php if (isset($_GET['uzenet'])): ?>
        <p style="color: green; font-weight: bold;">
            <?php echo htmlspecialchars($_GET['uzenet']); ?>
        </p>
    <?php endif; ?>

    <?php if (isset($_GET['confirm_replace']) && isset($_GET['kategoria'])): ?>
    <form method="post" action="lejatszasi_lista_kategoria.php">
        <input type="hidden" name="kategoria_id" value="<?php echo (int)$_GET['kategoria']; ?>">
        <input type="hidden" name="megerosites" value="1">
        <p style="color: red;">Már létezik ilyen nevű lejátszási lista. Szeretnéd lecserélni?</p>
        <button type="submit">Lecserélem</button>
        <a href="videok.php?kategoria=<?php echo (int)$_GET['kategoria']; ?>"><button type="button">Mégse</button></a>
    </form>
<?php endif; ?>



<div style="max-width: 400px; text-align: left;">
    <!-- Keresési űrlap -->
    <form method="GET" action="videok.php" style="margin-bottom: 10px;">
        <label for="kulcsszo">Kulcsszó:</label>
        <input type="text" id="kulcsszo" name="kulcsszo" value="<?php echo htmlspecialchars($kulcsszo); ?>">

        <label for="kategoria">Kategória:</label>
        <select name="kategoria" id="kategoria">
            <option value="">-- Mind --</option>
            <?php foreach ($kategoriak as $kat): ?>
                <option value="<?php echo $kat['KATEGORIA_ID']; ?>" <?php if ($kategoria_id == $kat['KATEGORIA_ID']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($kat['NEV']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php if (isset($_SESSION['user_id'])): ?>
            <label><input type="checkbox" name="csak_kedvencek" <?php echo $csak_kedvencek ? 'checked' : ''; ?>> Csak kedvenceim</label>
        <?php endif; ?>

        <button type="submit">Keresés</button>
    </form>

    <!-- Lejátszási lista létrehozása -->
    <?php if (isset($_SESSION['user_id']) && !empty($kategoria_id)): ?>
        <form method="post" action="lejatszasi_lista_kategoria.php">
            <input type="hidden" name="kategoria_id" value="<?php echo htmlspecialchars($kategoria_id); ?>">
            <button type="submit">Lejátszási lista létrehozása</button>
        </form>
    <?php endif; ?>
</div>


<h2>Találatok:</h2>
<?php if (count($talalatok) === 0): ?>
    <p>Nincs találat.</p>
<?php else: ?>
    <div style="max-height: 600px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; margin-bottom: 20px;">
        <ul style="margin: 0; padding-left: 20px;">
            <?php foreach ($talalatok as $video): ?>
                <li style="margin-bottom: 15px;">
                    <strong><?php echo htmlspecialchars($video['CIM']); ?></strong> (<?php echo htmlspecialchars($video['KATEGORIA_NEV']); ?>)<br>
                    <?php echo htmlspecialchars($video['LEIRAS']); ?><br>
                    <a href="<?php echo htmlspecialchars($video['VIDEO_URL']); ?>" target="_blank">Videó megtekintése</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>


    <h2>Legújabb 5 videó:</h2>
    <ul>
        <?php foreach ($uj_videok as $video): ?>
            <li>
            <strong><?php echo htmlspecialchars($video['CIM']); ?></strong><br>
    Kategória: <?php echo htmlspecialchars($video['KATEGORIA_NEV']); ?><br>
    Feltöltés: <?php echo date('Y. m. d.', strtotime($video['FELTOLTES_DATUM'])); ?><br>
    Kedvencelés: <?php echo (int)$video['KEDVENC_DB']; ?> alkalommal<br>
    <a href="<?php echo htmlspecialchars($video['VIDEO_URL']); ?>" target="_blank">Videó megtekintése</a>
            </li>
        <?php endforeach; ?>
    </ul>

    <h2>Legnézettebb 5 videó:</h2>
<ul>
    <?php foreach ($nezett_videok as $video): ?>
        <li>
            <strong><?php echo htmlspecialchars($video['CIM']); ?></strong><br>
            Kategóriák: <?php echo htmlspecialchars($video['KATEGORIAK']); ?><br>
            Megtekintések száma: <?php echo (int)$video['MEGTEKINTES_SZAM']; ?> alkalom
        </li>
    <?php endforeach; ?>
</ul>

    <h2>Videók kategóriánként:</h2>
<ul>
    <?php foreach ($statisztika as $sor): ?>
        <li><?php echo htmlspecialchars($sor['KATEGORIA_NEV']) . ': ' . $sor['VIDEO_DB']; ?> db videó</li>
    <?php endforeach; ?>
</ul>
</body>
</html>
