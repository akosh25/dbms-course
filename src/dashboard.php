<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
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

        <form action="logout.php" method="post">
            <div class="form-group">
                <button type="submit">Kilépés</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>