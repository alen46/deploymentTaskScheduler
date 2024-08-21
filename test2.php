<?php
require("conn.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {  
        if($_POST['type'] == 'date'){
            $from = $_POST['from'];
            $to = $_POST['to'];
            $stmt = $conn->prepare("SELECT required_days,username,deployment_date,portalname,purl FROM `deployment` INNER JOIN portal ON deployment.portal_id = portal.pid INNER JOIN users ON users.userid =  portal.portal_owner WHERE deployment.deployment_date BETWEEN :from AND :to ");
            $stmt->bindParam(':from', $from);
            $stmt->bindParam(':to', $to);
        }
        elseif($_POST['type'] == 'user'){
            $usr = $_POST['usr'];
            $stmt = $conn->prepare("SELECT required_days,username,deployment_date,portalname,purl FROM `deployment` INNER JOIN portal ON deployment.portal_id = portal.pid INNER JOIN users ON users.userid =  portal.portal_owner WHERE users.userid = :usr ");
            $stmt->bindParam(':usr', $usr);
        }
        elseif($_POST['type'] == 'portal'){
            $portl = $_POST['portl'];
            $stmt = $conn->prepare("SELECT required_days,username,deployment_date,portalname,purl FROM `deployment` INNER JOIN portal ON deployment.portal_id = portal.pid INNER JOIN users ON users.userid =  portal.portal_owner WHERE portal.pid = :portl ");
            $stmt->bindParam(':portl', $portl);
        }

        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($data)) {  
            echo json_encode($data);
        } else {
            echo json_encode(array("message" => "No data found"));
        }
    }catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
}

elseif ($_SERVER["REQUEST_METHOD"] === "GET") {
    try {  
        if($_GET['type'] == 'alldeployments'){
            $stmt = $conn->prepare("SELECT required_days,username,deployment_date,portalname,purl FROM `deployment` INNER JOIN portal ON deployment.portal_id = portal.pid INNER JOIN users ON users.userid = portal.portal_owner;");
        }
        elseif($_GET['type'] == 'change'){
            $stmt = $conn->prepare("SELECT changelog.change_date as ChangeDate, portal.portalname as PortalName, portal.purl PortalURL, changelog.old_date as OldDate, changelog.new_date as NewDate, changelog.info AS ChangeInfo FROM `changelog` INNER JOIN deployment ON changelog.deployment_id = deployment.deployment_id INNER JOIN portal on portal.pid = deployment.portal_id;");
        }elseif($_GET['type'] == 'olddeployments'){
            $stmt = $conn->prepare("SELECT deployment_log.oldversion,username,deployment_log.date,portalname,purl, portal.version FROM `deployment_log` INNER JOIN portal ON deployment_log.portal_id = portal.pid INNER JOIN users ON users.userid = portal.portal_owner;");
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($data)) {
            echo json_encode($data);
        } else {
            echo json_encode(array("message" => "No data found"));
        }
    }catch(PDOException $e){
        echo "Connection failed: " . $e->getMessage();
    }
}

