<!-- menu.php -->
<style>
    .menu-container {
        position: absolute;
        top: 10px;
        left: 10px;
    }

    .menu-button {
        padding: 10px 20px;
        font-size: 16px;
        cursor: pointer;
        background-color: #007BFF;
        color: white;
        border: none;
        border-radius: 5px;
    }

    .menu-items {
        display: none;
        position: absolute;
        top: 45px;
        left: 0;
        background-color: #f2f2f2;
        border: 1px solid #ccc;
        border-radius: 4px;
        padding: 10px;
        z-index: 1000;
    }

    .menu-items a {
        display: block;
        margin-bottom: 8px;
        color: #333;
        text-decoration: none;
    }

    .menu-items a:hover {
        text-decoration: underline;
    }
</style>

<div class="menu-container">
    <button class="menu-button" id="menuButton" onclick="toggleMenu()">Menü</button>
    <div class="menu-items" id="menuItems">
        <a href="register.php">Regisztráció</a>
        <a href="login.php">Bejelentkezés</a>
        <a href="videok.php">Videók</a>
        <a href="uj_video_beszur.php">Videó feltöltés</a>
        <a href="dashboard.php">Profil</a>
        <a href="lekerdezesek.php">Alapadatok</a>
        <a href="logout.php">Kilépés</a>
    </div>
</div>

<script>
    function toggleMenu() {
        const menu = document.getElementById("menuItems");
        menu.style.display = menu.style.display === "block" ? "none" : "block";
    }

    document.addEventListener("click", function(event) {
        const menu = document.getElementById("menuItems");
        const button = document.getElementById("menuButton");
        if (!button.contains(event.target) && !menu.contains(event.target)) {
            menu.style.display = "none";
        }
    });
</script>
