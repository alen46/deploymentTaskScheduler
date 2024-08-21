<?php
require("conn.php");
$today = new DateTime();
$formattedDate = $today->format('Y-m-d');
$sql = "SELECT * from deployment where CURRENT_DATE >= DATE_ADD(deployment.deployment_date, INTERVAL deployment.required_days DAY)";
$res = $conn->query($sql);
foreach ($res as $row) {
    echo $row['deployment_id'];
    $sqll = 'UPDATE `portal` SET `version`=:version,`pfeatures`=:features WHERE portal.pid = :pid';
    $stmt = $conn->prepare($sqll);
    $stmt->bindParam('features', $row['deployment_note']);
    $stmt->bindParam('version', $row['deployment_version']);
    $stmt->bindParam('pid', $row['portal_id']);

    $sql1 = 'select * from portal where pid = :pid';
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bindParam('pid', $row['portal_id']);
    $res1 = $stmt1->execute();
    $ress = $stmt1->fetch(PDO::FETCH_ASSOC);

    $sql2 = 'INSERT INTO `deployment_log`(`portal_id`, `oldversion`, `oldfeatures`, `date`) VALUES (:portal,:oldVersion,:oldFeatures,CURRENT_DATE)';
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bindParam('portal', $ress['pid']);
    $stmt2->bindParam('oldVersion', $ress['version']);
    $stmt2->bindParam('oldFeatures', $ress['pfeatures']);

    $sqlDeleteRelated = 'DELETE FROM `schhedulechange` WHERE `deployment_id` = :did';
    $stmtDeleteRelated = $conn->prepare($sqlDeleteRelated);
    $stmtDeleteRelated->bindParam('did', $row['deployment_id']);
    $stmtDeleteRelated->execute();

    $sqlDeleteRelated2 = 'DELETE FROM `changelog` WHERE `deployment_id` = :did';
    $stmtDeleteRelated2 = $conn->prepare($sqlDeleteRelated2);
    $stmtDeleteRelated2->bindParam('did', $row['deployment_id']);
    $stmtDeleteRelated2->execute();

    $sql3 = 'DELETE FROM `deployment` WHERE deployment_id = :did ';
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bindParam('did', $row['deployment_id']);

    $stmt->execute();
    $stmt2->execute();
    $stmt3->execute();
}
