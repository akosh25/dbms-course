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

function lekerdezes_tablazat($conn, $sql, $cim) {
    $stmt = oci_parse($conn, $sql);
    oci_execute($stmt);

    echo "<h3>{$cim}</h3>";
    echo "<table border='1' cellpadding='5' cellspacing='0' style='margin-bottom: 20px;'>";
    echo "<tr>";
    $ncols = oci_num_fields($stmt);
    for ($i = 1; $i <= $ncols; $i++) {
        $colname = oci_field_name($stmt, $i);
        echo "<th>" . htmlspecialchars($colname) . "</th>";
    }
    echo "</tr>";

    while ($row = oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_NULLS)) {
        echo "<tr>";
        foreach ($row as $item) {
            echo "<td>" . htmlspecialchars($item !== null ? $item : "&nbsp;") . "</td>";
        }
        echo "</tr>";
    }

    echo "</table>";
    oci_free_statement($stmt);
}

include 'menu.php';

echo "<div class='container'>";

lekerdezes_tablazat($conn, "SELECT * FROM Felhasznalo", "Minden felhasználó adat");
lekerdezes_tablazat($conn, "SELECT cim, hossz FROM Video", "Minden videó címe és hossza");
lekerdezes_tablazat($conn, "SELECT nev FROM Kategoria", "Minden kategória neve");
lekerdezes_tablazat($conn, "SELECT nev FROM Cimke", "Minden címke neve");
lekerdezes_tablazat($conn, "SELECT cim FROM Video WHERE is_short = 1", "Rövid videók");
lekerdezes_tablazat($conn, "SELECT felhasznalonev FROM Felhasznalo WHERE profilkep_url IS NOT NULL", "Felhasználók profilképpel");
lekerdezes_tablazat($conn, "SELECT nev FROM Kategoria WHERE leiras IS NOT NULL", "Kategóriák leírással");
lekerdezes_tablazat($conn, "SELECT cim, feltoltes_datum FROM Video ORDER BY feltoltes_datum DESC", "Videók feltöltési dátum szerint");
lekerdezes_tablazat($conn, "SELECT felhasznalonev FROM Felhasznalo WHERE szerepkor = 'admin'", "Admin felhasználók");
lekerdezes_tablazat($conn, "SELECT cim FROM Video WHERE hossz > 600", "10 percnél hosszabb videók");
lekerdezes_tablazat($conn, "SELECT COUNT(*) AS felhasznalo_szam FROM Felhasznalo", "Felhasználók száma");
lekerdezes_tablazat($conn, "SELECT * FROM (SELECT * FROM Video ORDER BY cim) WHERE ROWNUM <= 5", "Első 5 videó ABC sorrendben");

oci_close($conn);

echo "</div>";
?>
