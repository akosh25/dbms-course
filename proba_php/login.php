<?php
// munkamenet indítása
session_start();

// üzenetek inicializálása
$error_message = "";
$success_message = "";

// űrlap beküldése
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Adatbáziskapcsolat értékei
    $host = "localhost";
    $port = "1521";
    $sid = "xe"; 
    $username = "LOGIN";
    $password = "oracle";
    
    // email, jelszó bekérése
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $input_password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Mezők kitöltésének ellenőrzése
    if (empty($email) || empty($input_password)) {
        $error_message = "Sikertelen bejelentkezés";
    } else {
            // Adatbáziskapcsolat létrehozása
            $conn_string = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=$host)(PORT=$port))(CONNECT_DATA=(SID=$sid)))";
            $conn = oci_connect($username, $password, $conn_string, 'AL32UTF8');
                
            // Ha nem sikerült az adatbáziskapcsolat
            if (!$conn) {
                $e = oci_error();
                die("<p style='color: red;'>Adatbázis-kapcsolat sikertelen: " . $e['message'] . "</p>");
            }

            
            // Email alapján felhasználót keres
            $sql = "SELECT felhasznalo_id, email, jelszo, felhasznalonev, szerepkor FROM Felhasznalo WHERE email = :email";
            $stmt = oci_parse($conn, $sql);
            
            // SQL befecskendezés elkerülése
            oci_bind_by_name($stmt, ":email", $email);
            
            // Lekérdezés végrehajtása
            oci_execute($stmt);
            
            // Eredmény lekérése
            $user = oci_fetch_assoc($stmt);

            // SHA1-es kódolás a jelszó összehasonlításához
            $hashed_password = sha1($input_password);
            

            // Ha a felhasználó létezik és a jelszó helyes
            if ($user && $hashed_password === $user['JELSZO']) {
                    $success_message = "Sikeres bejelentkezés";
                    
                    // Felhasználó adatainak mentése a munkamenetbe
                    $_SESSION['user_id'] = $user['FELHASZNALO_ID'];
                    $_SESSION['username'] = $user['FELHASZNALONEV'];
                    $_SESSION['email'] = $user['EMAIL'];
                    $_SESSION['szerepkor'] = $user["SZEREPKOR"];
                    
                    // Utolsó bejelentkezés időpontjának frissítése
                    $update_sql = "UPDATE Felhasznalo SET utolso_bejelentkezes = SYSTIMESTAMP WHERE felhasznalo_id = :user_id";
                    $update_stmt = oci_parse($conn, $update_sql);
                    oci_bind_by_name($update_stmt, ":user_id", $user['FELHASZNALO_ID']);
                    oci_execute($update_stmt);

                    // átirányítás a dashboard.phph-ra
                    header("Location: dashboard.php");
                    exit;
                    
                } 
                else {
                    // Ha a bejelentkezési adatok hibásak
                    $error_message = "Sikertelen bejelentkezés";
                }
            
            oci_free_statement($stmt);
            oci_close($conn);           
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés - Videó Megosztó Platform</title>
    <link rel="stylesheet" href="style.css">

    

</head>
<body>
<?php include 'menu.php'; ?>

    <div class="container">
        <div class="login-form">
            <h2>Bejelentkezés</h2>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="email">Email cím:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Jelszó:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <button type="submit">Bejelentkezés</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>