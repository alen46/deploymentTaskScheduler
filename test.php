<?php
require("conn.php");
$newDate = $_POST['new_date'];
$oldDate  = $_POST['old_date'];
$changeDescription  = $_POST['info'];
$deploymentId = $_POST['deployment_id'];

$sql = "UPDATE schhedulechange SET change_status= :status WHERE schhedulechange.deployment_id  = :deployment_id";
$stmt1 = $conn->prepare($sql);
$stmt1->bindParam(":status", $_POST['status']);
$stmt1->bindParam(":deployment_id", $_POST['deployment_id']);
$stmt1->execute();

$sqlUpdate = "UPDATE deployment SET deployment_date = :newDate WHERE deployment_id = :deploymentId";
$stmt2 = $conn->prepare($sqlUpdate);
$stmt2->bindParam(':newDate', $newDate);
$stmt2->bindParam(':deploymentId', $deploymentId);
$stmt2->execute();

$sqlInsertChangelog = "INSERT INTO `changelog`(`deployment_id`, `old_date`, `new_date`, `change_date`, `change_time`, `info`) VALUES  (:deploymentId, :oldDate, :newDate,:changedate, :changetime, :changeDescription)";
$stmtChangelog = $conn->prepare($sqlInsertChangelog);
$stmtChangelog->bindParam(':deploymentId', $deploymentId);
$stmtChangelog->bindParam(':oldDate', $oldDate);
$stmtChangelog->bindParam(':newDate', $newDate);
$stmtChangelog->bindParam(':changeDescription', $changeDescription);
$datenow = new DateTime();
$formattedDate = $datenow->format('Y-m-d');
$timenow = new DateTime();
$formattedTime = $timenow->format('H:i:s');
$stmtChangelog->bindParam(':changedate', $formattedDate);
$stmtChangelog->bindParam(':changetime', $formattedTime);
$stmtChangelog->execute();

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
                    $sqlInsertChangelog2 = "INSERT INTO `changelog`(`deployment_id`, `old_date`, `new_date`, `change_date`, `change_time`, `info`) VALUES  (:deploymentId, :oldDate, :newDate,:changedate, :changetime, :changeDescription)";
                    $stmtChangelog2 = $conn->prepare($sqlInsertChangelog2);
                    $stmtChangelog2->bindParam(':deploymentId', $dateRow['deployment_id']);
                    $stmtChangelog2->bindParam(':oldDate', $dateRow['deployment_date']);
                    $stmtChangelog2->bindParam(':newDate', $newStartDate);
                    $stmtChangelog2->bindValue(':changeDescription', 'adjusted due to deployment conflict');
                    $datenow = new DateTime();
                    $formattedDate = $datenow->format('Y-m-d');

                    $timenow = new DateTime();
                    $formattedTime = $timenow->format('H:i:s');

                    $stmtChangelog2->bindParam(':changedate', $formattedDate);
                    $stmtChangelog2->bindParam(':changetime', $formattedTime);
                    $stmtChangelog2->execute();
                    $queue[] = $dateRow['deployment_id'];
                }
            }
        }
        $processedIds[] = $depid;
    }
}
$initialId = $deploymentId;
updateDeploymentDates($conn, $initialId);
echo json_encode(array("response" => "Deployment dates have been updated."));
