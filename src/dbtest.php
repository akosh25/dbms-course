<?php

$host = "localhost";
$port = "1521";
$sid = "xe"; 
$username = "LOGIN";
$password = "oracle";


$conn_string = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=$host)(PORT=$port))(CONNECT_DATA=(SID=$sid)))";
$conn = oci_connect($username, $password, $conn_string, 'AL32UTF8');


if (!$conn) {
    $e = oci_error();
    echo "<p style='color: red;'>Sikertelen adatbáziskapcsolat: " . $e['message'] . "</p>";
} else {
    echo "<p style='color: green;'>Sikeresen csatlakozott az adatbázishoz!</p>";
}


oci_close($conn);
?>
