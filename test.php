<?php

function updateDeploymentDates($conn, $initialId) {
    $queue = [$initialId];
    $processedIds = [];
    while (!empty($queue)) {
        $depid = array_shift($queue);
        if (in_array($depid, $processedIds)) {
            continue;
        }
        $curr = "SELECT deployment_id, deployment_date, required_days FROM `deployment` WHERE deployment_id = :depid";
        $stmtcur = $conn->prepare($curr);
        $stmtcur->bindParam(":depid", $depid, PDO::PARAM_INT);
        $stmtcur->execute();
        $cur = $stmtcur->fetch(PDO::FETCH_ASSOC);
        if (!$cur) {
            continue;
        }
        $startDate = new DateTime($cur['deployment_date']);
        $endDate = (clone $startDate)->modify('+' . ($cur['required_days'] - 1) . ' days');
        $selectdates = "SELECT deployment_id, deployment_date, required_days FROM `deployment` WHERE deployment_id <> :depid";
        $stmtseldates = $conn->prepare($selectdates);
        $stmtseldates->bindParam(":depid", $depid, PDO::PARAM_INT);
        $stmtseldates->execute();
        $dates = $stmtseldates->fetchAll(PDO::FETCH_ASSOC);
        $overlaps = false;
        foreach ($dates as $dateRow) {
            $startDate2 = new DateTime($dateRow['deployment_date']);
            $endDate2 = (clone $startDate2)->modify('+' . ($dateRow['required_days'] - 1) . ' days');
            if ($startDate <= $endDate2 && $startDate2 <= $endDate) {
                $overlaps = true;
                $newStartDate = (clone $endDate)->modify('+1 day')->format('Y-m-d');
                $change = "UPDATE `deployment` SET `deployment_date` = :newDate WHERE deployment_id = :did";
                $stmtchange = $conn->prepare($change);
                $stmtchange->bindParam(":newDate", $newStartDate);
                $stmtchange->bindParam(":did", $dateRow['deployment_id'], PDO::PARAM_INT);
                if ($stmtchange->execute()) {
                    $queue[] = $dateRow['deployment_id'];
                }
            }
        }
        $processedIds[] = $depid;
    }
}
require("conn.php");
$initialId = 4;
updateDeploymentDates($conn, $initialId);
echo "Deployment dates have been updated.";
