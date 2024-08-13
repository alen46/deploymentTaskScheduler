<?php
$servername = "localhost";
$dbname = "dscheduler";
$username = "root";
$password = "";
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(array("response" =>"Connection failed: " . $e->getMessage()));
}
