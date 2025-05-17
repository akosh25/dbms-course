<?php
session_start();

// csak admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}


$success_message = "";
$error_message = "";

// űrlap
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nev = trim($_POST["nev"] ?? "");
    $leiras = trim($_POST["leiras"] ?? "");

    if (empty($nev)) {
        $error_message = "A kategórianév megadása kötelező.";
    } else {
        
        $host = "localhost";
        $port = "1521";
        $sid = "xe"; 
        $username = "LOGIN";
        $password = "oracle";

        $conn_string = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=$host)(PORT=$port))(CONNECT_DATA=(SID=$sid)))";
        $conn = oci_connect($username, $password, $conn_string, 'AL32UTF8');

        if (!$conn) {
            $e = oci_error();
            $error_message = "Adatbázis hiba: " . $e['message'];
        } else {
            $sql = "INSERT INTO Kategoria (kategoria_id, nev, leiras) 
                    VALUES (kategoria_seq.NEXTVAL, :nev, :leiras)";
            $stmt = oci_parse($conn, $sql);
            oci_bind_by_name($stmt, ":nev", $nev);
            oci_bind_by_name($stmt, ":leiras", $leiras);

            if (oci_execute($stmt)) {
                $success_message = "Kategória sikeresen hozzáadva!";
            } else {
                $e = oci_error($stmt);
                $error_message = "Hiba történt: " . $e['message'];
            }

            oci_free_statement($stmt);
            oci_close($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Kategória hozzáadása</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="login-form">
        <h2>Kategória hozzáadása</h2>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="nev">Kategória neve:</label>
                <input type="text" id="nev" name="nev" required>
            </div>

            <div class="form-group">
                <label for="leiras">Leírás:</label>
                <input type="text" id="leiras" name="leiras">
            </div>

            <div class="form-group">
                <button type="submit">Hozzáadás</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>