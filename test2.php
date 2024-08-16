<?php
require("conn.php");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
try {  
    $from = $_POST['from'];
    $to = $_POST['to'];
    if($_POST['type'] == 'date'){
        $stmt = $conn->prepare("SELECT * FROM `deployment` INNER JOIN portal ON deployment.portal_id = portal.pid INNER JOIN users ON users.userid =  portal.portal_owner WHERE deployment.deployment_date BETWEEN :from AND :to ");
        $stmt->bindParam(':from', $from);
        $stmt->bindParam(':to', $to);
    }

    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($data)) {
        header('Content-Type: application/json');
        echo json_encode($data);
    } else {
        echo json_encode(array("message" => "No data found"));
    }

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

}
elseif ($_SERVER["REQUEST_METHOD"] === "GET") {
    try {  
        if($_GET['type'] == 'alldeployments'){
            $stmt = $conn->prepare("SELECT required_days,username,deployment_date,portalname,purl FROM `deployment` INNER JOIN portal ON deployment.portal_id = portal.pid INNER JOIN users ON users.userid = portal.portal_owner;");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($data)) {
                header('Content-Type: application/json');
                echo json_encode($data);
            } else {
                echo json_encode(array("message" => "No data found"));
            }
    }
    } catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
}


// $csvFileName = 'reports/report.csv';
// $file = fopen($csvFileName, 'w');
// $query = $conn->query("SELECT deployment_version as DeploymentVersion,deployment_date as DeploymentDate, deployment_note as DeploymentNote, required_days as RequiredDays,portalname as PortalName, purl as PortalURL, version as CurrentPortalVersion, pfeatures as PortalFeatures, username as PortalOwner FROM `deployment` INNER JOIN portal ON deployment.portal_id = portal.pid INNER JOIN users on portal.portal_owner = users.userid ");
// $headers = array_keys($query->fetch(PDO::FETCH_ASSOC));
// fputcsv($file, $headers);
// $query->execute();
// while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
//     fputcsv($file, $row);
// }
// fclose($file);
// $conn = null;

// echo "CSV file created successfully: $csvFileName";
// ?>
