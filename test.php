<?php
require("conn.php");

$sql = "SELECT deployment_date, required_days FROM `deployment`;";
$stmt = $conn->prepare($sql);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);  // Fetch all rows as an associative array

$allDates = [];  // Initialize an array to hold all dates

foreach ($data as $row) {
    $initialDate = new DateTime($row['deployment_date']);
    $datearr = [];

    for ($i = 0; $i < $row['required_days']; $i++) {
        $date = clone $initialDate;  // Clone the initial date to avoid cumulative modifications
        $date->modify('+' . $i . ' days');
        array_push($datearr, $date->format('Y-m-d'));  // Add formatted date to array
    }

    $allDates = array_merge($allDates, $datearr);  // Merge the current row dates into the main array
}

echo json_encode($allDates);  // Output all dates as a single JSON array

?>


