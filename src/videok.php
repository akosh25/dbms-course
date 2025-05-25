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
$legutobbi_komment = isset($_GET['legutobbi_komment']);


if ($legutobbi_komment) {
    $sql = "
      SELECT *
      FROM (
        SELECT
          v.video_id,
          v.cim,
          v.leiras,
          
          COALESCE(MIN(k.nev), 'Nincs kategória') AS kategoria_nev,
          v.video_url,
          hsub.hozzaszolas_szoveg AS utolso_komment,
          hsub.letrehozas_datum  AS utolso_datum
        FROM Video v
        JOIN (
          SELECT hozzaszolas_szoveg, letrehozas_datum, video_id
          FROM (
            SELECT hozzaszolas_szoveg,
                   letrehozas_datum,
                   video_id,
                   ROW_NUMBER() OVER (PARTITION BY video_id ORDER BY letrehozas_datum DESC) AS rn
            FROM Hozzaszolas
          )
          WHERE rn = 1
        ) hsub ON v.video_id = hsub.video_id
        LEFT JOIN VideoKategoria vk ON v.video_id = vk.video_id
        LEFT JOIN Kategoria k       ON vk.kategoria_id = k.kategoria_id
        GROUP BY
          v.video_id,
          v.cim,
          v.leiras,
          v.video_url,
          hsub.hozzaszolas_szoveg,
          hsub.letrehozas_datum
        ORDER BY hsub.letrehozas_datum DESC
      )
      WHERE ROWNUM <= 5
    ";
}
 else {
    // Alapértelmezett szűrés kulcsszó és kategória szerint
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
        $sql .= " AND (LOWER(v.cim) LIKE '%'||LOWER(:kulcsszo)||'%' OR LOWER(v.leiras) LIKE '%'||LOWER(:kulcsszo)||'%')";
    }
    if (!empty($kategoria_id)) {
        $sql .= " AND EXISTS(
            SELECT 1 FROM VideoKategoria vk2
            WHERE vk2.video_id = v.video_id
              AND vk2.kategoria_id = :kategoria_id
        )";
    }
    $sql .= " ORDER BY v.feltoltes_datum DESC";
}

// Lekérdezés előkészítése és bindolás
$stmt = oci_parse($conn, $sql);
if ($legutobbi_komment) {
    // nincs bind
} else {
    if ($csak_kedvencek) {
        oci_bind_by_name($stmt, ":felhasznalo_id", $_SESSION['user_id']);
    }
    if (!empty($kulcsszo)) {
        oci_bind_by_name($stmt, ":kulcsszo", $kulcsszo);
    }
    if (!empty($kategoria_id)) {
        oci_bind_by_name($stmt, ":kategoria_id", $kategoria_id);
    }
}
oci_execute($stmt);

// Eredmények lekérése
$talalatok = [];
while ($row = oci_fetch_assoc($stmt)) {
    $talalatok[] = $row;
}
oci_free_statement($stmt);

// Eredményeken kívüli statisztikák továbbra is
// Legújabb 5 videó
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

// Videók kategóriánként
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

// Legnézettebb 5 videó
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


// 1) Átlagos videóhossz kategóriánként
$atlag_hossz = [];
$atlag_stmt = oci_parse($conn, "
    SELECT
      k.nev                AS kategoria_nev,
      ROUND(AVG(v.hossz),2) AS ATLAG_HOSSZ
    FROM Video v
    JOIN VideoKategoria vk ON v.video_id = vk.video_id
    JOIN Kategoria k      ON vk.kategoria_id = k.kategoria_id
    GROUP BY k.nev
    ORDER BY ATLAG_HOSSZ DESC
");
oci_execute($atlag_stmt);
while ($row = oci_fetch_assoc($atlag_stmt)) {
    $atlag_hossz[] = $row;
}
oci_free_statement($atlag_stmt);

// 2) Felhasználók kommentszáma és legutolsó komment dátuma
$aktiv_felhasznalok = [];
$user_stmt = oci_parse($conn, "
    SELECT
      u.felhasznalonev          AS felhasznalonev,
      COUNT(h.hozzaszolas_id)   AS komment_szam,
      MAX(h.letrehozas_datum)   AS legutolso_komment
    FROM Felhasznalo u
    LEFT JOIN Hozzaszolas h
      ON u.felhasznalo_id = h.felhasznalo_id
    GROUP BY u.felhasznalonev
    HAVING COUNT(h.hozzaszolas_id) > 0
    ORDER BY komment_szam DESC, legutolso_komment DESC
");
oci_execute($user_stmt);
while ($row = oci_fetch_assoc($user_stmt)) {
    $aktiv_felhasznalok[] = $row;
}
oci_free_statement($user_stmt);

// Most zárd be a kapcsolatot
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
        <p style="color: green; font-weight: bold;"><?= htmlspecialchars($_GET['uzenet']) ?></p>
    <?php endif; ?>
    <?php if (isset($_GET['confirm_replace']) && isset($_GET['kategoria'])): ?>
    <form method="post" action="lejatszasi_lista_kategoria.php">
        <input type="hidden" name="kategoria_id" value="<?= (int)$_GET['kategoria'] ?>">
        <input type="hidden" name="megerosites" value="1">
        <p style="color: red;">Már létezik ilyen nevű lejátszási lista. Szeretnéd lecserélni?</p>
        <button type="submit">Lecserélem</button>
        <a href="videok.php?kategoria=<?= (int)$_GET['kategoria'] ?>"><button type="button">Mégse</button></a>
    </form>
    <?php endif; ?>
    <div style="max-width:400px; text-align:left;">
    <form method="GET" action="videok.php" style="margin-bottom:10px;">
        <label for="kulcsszo">Kulcsszó:</label>
        <input type="text" id="kulcsszo" name="kulcsszo" value="<?= htmlspecialchars($kulcsszo) ?>">
        <label for="kategoria">Kategória:</label>
        <select id="kategoria" name="kategoria">
            <option value="">-- Mind --</option>
            <?php foreach($kategoriak as $kat): ?>
            <option value="<?= $kat['KATEGORIA_ID'] ?>" <?= $kategoria_id==$kat['KATEGORIA_ID']?'selected':'' ?>>
                <?= htmlspecialchars($kat['NEV']) ?>
            </option>
            <?php endforeach; ?>
        </select><br>
        <?php if(isset($_SESSION['user_id'])): ?>
        <label><input type="checkbox" name="csak_kedvencek" value="1" <?= $csak_kedvencek?'checked':'' ?>> Csak kedvenceim</label><br>
        <?php endif; ?>
        <label><input type="checkbox" name="legutobbi_komment" value="1" <?= $legutobbi_komment?'checked':'' ?>> Utolsó hozzászólások</label><br>
        <button type="submit">Keresés</button>
    </form>
    <?php if(isset($_SESSION['user_id'])&& $kategoria_id): ?>
    <form method="post" action="lejatszasi_lista_kategoria.php">
        <input type="hidden" name="kategoria_id" value="<?= htmlspecialchars($kategoria_id) ?>">
        <button type="submit">Lejátszási lista létrehozása</button>
    </form>
    <?php endif; ?>
    </div>
    <h2>Találatok:</h2>
    <?php if(empty($talalatok)): ?>
        <p>Nincs találat.</p>
    <?php else: ?>
        <div style="max-width: 800px; width: 100%; margin: 0 auto 20px; max-height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;">
        <ul style="margin:0;padding-left:20px;">
        <?php foreach($talalatok as $video): ?>
            <li style="margin-bottom:15px;">
                <strong><?= htmlspecialchars($video['CIM']) ?></strong> (<?= htmlspecialchars($video['KATEGORIA_NEV']) ?>)<br>
                <?= htmlspecialchars($video['LEIRAS']) ?><br>
                <?php if($legutobbi_komment): ?>
                    <em>Utolsó hozzászólás (<?= date('Y.m.d H:i',strtotime($video['UTOLSO_DATUM'])) ?>): <?= htmlspecialchars($video['UTOLSO_KOMMENT']) ?></em><br>
                <?php endif; ?>
                <a href="<?= htmlspecialchars($video['VIDEO_URL']) ?>" target="_blank">Videó megtekintése</a>
            </li>
        <?php endforeach; ?>
        </ul>
        </div>
    <?php endif; ?>
    <h2>Legújabb 5 videó:</h2>
    <ul>
        <?php foreach($uj_videok as $video): ?>
        <li>
            <strong><?= htmlspecialchars($video['CIM']) ?></strong><br>
            Kategória: <?= htmlspecialchars($video['KATEGORIA_NEV']) ?><br>
            Feltöltés: <?= date('Y. m. d.',strtotime($video['FELTOLTES_DATUM'])) ?><br>
            Kedvencelés: <?= (int)$video['KEDVENC_DB'] ?> alkalommal<br>
            <a href="<?= htmlspecialchars($video['VIDEO_URL']) ?>" target="_blank">Videó megtekintése</a>
        </li>
        <?php endforeach; ?>
    </ul>
    <h2>Legnézettebb 5 videó:</h2>
    <ul>
        <?php foreach($nezett_videok as $video): ?>
        <li>
            <strong><?= htmlspecialchars($video['CIM']) ?></strong><br>
            Kategóriák: <?= htmlspecialchars($video['KATEGORIAK']) ?><br>
            Megtekintések száma: <?= (int)$video['MEGTEKINTES_SZAM'] ?> alkalom
        </li>
        <?php endforeach; ?>
    </ul>
    <h2>Videók kategóriánként:</h2>
    <div style="max-width: 800px; width: 100%; margin: 0 auto 20px; max-height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;">
    <ul>
        <?php foreach($statisztika as $sor): ?>
        <li><?= htmlspecialchars($sor['KATEGORIA_NEV']) ?>: <?= $sor['VIDEO_DB'] ?> db videó</li>
        <?php endforeach; ?>
    </ul>
        </div>
        <h2>Videók átlagos hossza kategóriánként:</h2>
        <div style="max-width: 800px; width: 100%; margin: 0 auto 20px; max-height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;">
    <ul>
    <?php foreach($atlag_hossz as $sor): ?>
        <li>
          <?= htmlspecialchars($sor['KATEGORIA_NEV']) ?> —
          átlagos hossz: <?= htmlspecialchars($sor['ATLAG_HOSSZ']) ?> mp
        </li>
    <?php endforeach; ?>
    </ul>
    </div>
    <h2>Aktív felhasználók kommentszáma és utolsó komment dátuma:</h2>
    <div style="max-width: 800px; width: 100%; margin: 0 auto 20px; max-height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;">
    <ul>
    <?php foreach($aktiv_felhasznalok as $u): ?>
        <li>
          <?= htmlspecialchars($u['FELHASZNALONEV']) ?> —
          kommentszám: <?= (int)$u['KOMMENT_SZAM'] ?>,
          utolsó komment: <?= date('Y.m.d H:i', strtotime($u['LEGUTOLSO_KOMMENT'])) ?>
        </li>
    <?php endforeach; ?>
    </ul>
    </div>
</body>
</html>
