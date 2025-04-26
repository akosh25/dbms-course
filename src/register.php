<?php
session_start();
$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $host = "localhost";
    $port = "1521";
    $sid = "xe";
    $username = "LOGIN";
    $password = "oracle";

    // Űrlap adatok
    $felhasznalonev = trim($_POST["felhasznalonev"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $jelszo = $_POST["password"] ?? "";
    $jelszo_ujra = $_POST["confirm_password"] ?? "";

    // Ellenőrzés
    if (empty($felhasznalonev) || empty($email) || empty($jelszo) || empty($jelszo_ujra)) {
        $error_message = "Minden mező kitöltése kötelező.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Érvénytelen email cím.";
    } elseif ($jelszo !== $jelszo_ujra) {
        $error_message = "A jelszavak nem egyeznek.";
    } else {
        $conn_string = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=$host)(PORT=$port))(CONNECT_DATA=(SID=$sid)))";
        $conn = oci_connect($username, $password, $conn_string, 'AL32UTF8');

        if (!$conn) {
            $e = oci_error();
            die("<p style='color:red;'>Adatbázis-kapcsolat hiba: " . $e['message'] . "</p>");
        }

        // email ellenőrzése
        $check_sql = "SELECT COUNT(*) AS CNT FROM Felhasznalo WHERE email = :email";
        $check_stmt = oci_parse($conn, $check_sql);
        oci_bind_by_name($check_stmt, ":email", $email);
        oci_execute($check_stmt);
        $row = oci_fetch_assoc($check_stmt);

        if ($row && $row['CNT'] > 0) {
            $error_message = "Ez az email már regisztrálva van.";
        } else {
            // Jelszó hash-elése
            $hashed_password = sha1($jelszo);

            // Felhasználó mentése
            $insert_sql = "INSERT INTO Felhasznalo (felhasznalo_id, felhasznalonev, email, jelszo, szerepkor, regisztracio_idopont, utolso_bejelentkezes, profilkep_url, bio)
               VALUES (felhasznalo_seq.NEXTVAL, :uname, :email, :pwd, 'user', SYSTIMESTAMP, NULL, NULL, NULL)";
            $insert_stmt = oci_parse($conn, $insert_sql);
            oci_bind_by_name($insert_stmt, ":uname", $felhasznalonev);
            oci_bind_by_name($insert_stmt, ":email", $email);
            oci_bind_by_name($insert_stmt, ":pwd", $hashed_password);

            if (oci_execute($insert_stmt)) {
                $success_message = "Sikeres regisztráció! Most már bejelentkezhetsz.";
            } else {
                $error_message = "Hiba történt a regisztráció során.";
            }
            oci_free_statement($insert_stmt);
        }

        oci_free_statement($check_stmt);
        oci_close($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Regisztráció - Videó Megosztó Platform</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="login-form">
        <h2>Regisztráció</h2>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group">
                <label for="felhasznalonev">Felhasználónév:</label>
                <input type="text" id="felhasznalonev" name="felhasznalonev" required>
            </div>

            <div class="form-group">
                <label for="email">Email cím:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Jelszó:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Jelszó megerősítése:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <div class="form-group">
                <button type="submit">Regisztráció</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>