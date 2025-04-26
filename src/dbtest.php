<?php


// kapcsolat
$servername = "localhost";
$username = "root"; 
$password = "";     
$dbname = "videomegoszto"; 


$conn = new mysqli($servername, $username, $password, $dbname);

// kapcsolat ellenőrzése
if ($conn->connect_error) {
    die("<h2>Hiba történt a csatlakozás során: " . $conn->connect_error . "</h2>");
} else {
    echo "<h2>Sikeres csatlakozás az adatbázishoz!</h2>";
}


$conn->close();
?>
