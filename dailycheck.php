<?php
/**
 * Check for finished deployments 
 * 
 * This script checks if any deployment has finished based on the deployment date and required days.
 * If a deployment is finished, it updates the portal with the new version and features, logs the change,
 * deletes related entries from the schedule change and changelog tables, and removes the deployment record.
 */
require("conn.php");
$today = new DateTime();
$formattedDate = $today->format('Y-m-d');

// Select deployments that have finished
$sql = "SELECT * from deployment where CURRENT_DATE >= DATE_ADD(deployment.deployment_date, INTERVAL deployment.required_days DAY)";
$res = $conn->query($sql);
foreach ($res as $row) {
    echo $row['deployment_id'];

    // Update the portal with the new version and features from the deployment
    $sqll = 'UPDATE `portal` SET `version`=:version,`pfeatures`=:features WHERE portal.pid = :pid';
    $stmt = $conn->prepare($sqll);
    $stmt->bindParam('features', $row['deployment_note']);
    $stmt->bindParam('version', $row['deployment_version']);
    $stmt->bindParam('pid', $row['portal_id']);

    // Fetch the current version and features of the portal before updating
    $sql1 = 'select * from portal where pid = :pid';
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bindParam('pid', $row['portal_id']);
    $res1 = $stmt1->execute();
    $ress = $stmt1->fetch(PDO::FETCH_ASSOC);

    // Log the previous version and features of the portal
    $sql2 = 'INSERT INTO `deployment_log`(`portal_id`, `oldversion`, `oldfeatures`, `date`) VALUES (:portal,:oldVersion,:oldFeatures,CURRENT_DATE)';
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bindParam('portal', $ress['pid']);
    $stmt2->bindParam('oldVersion', $ress['version']);
    $stmt2->bindParam('oldFeatures', $ress['pfeatures']);

    // Delete related entries from the schedule change table
    $sqlDeleteRelated = 'DELETE FROM `schhedulechange` WHERE `deployment_id` = :did';
    $stmtDeleteRelated = $conn->prepare($sqlDeleteRelated);
    $stmtDeleteRelated->bindParam('did', $row['deployment_id']);
    $stmtDeleteRelated->execute();

    // Delete related entries from the changelog table
    $sqlDeleteRelated2 = 'DELETE FROM `changelog` WHERE `deployment_id` = :did';
    $stmtDeleteRelated2 = $conn->prepare($sqlDeleteRelated2);
    $stmtDeleteRelated2->bindParam('did', $row['deployment_id']);
    $stmtDeleteRelated2->execute();
    
    // Delete the deployment record since it is now completed
    $sql3 = 'DELETE FROM `deployment` WHERE deployment_id = :did ';
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bindParam('did', $row['deployment_id']);

    $stmt->execute();
    $stmt2->execute();
    $stmt3->execute();
}
