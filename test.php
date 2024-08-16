<?php

// change deployment_date in deployment table and add to changelog table
// adjust dates of other deployments and add too changelog table 
// notify portal owmers about change
require('conn.php');
$newDate = $_POST['new_date'];
$oldDate  = $_POST['old_date'];
$changeDescription  = $_POST['info'];
            //'function':"managechange",
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

$sqlInsertChangelog = "INSERT INTO `changelog`(`deployment_id`, `old_date`, `new_date`, `change_date`, `change_time`, `info`) 
                        VALUES  (:deploymentId, :oldDate, :newDate,:changedate, :changetime, :changeDescription)";
$stmtChangelog = $conn->prepare($sqlInsertChangelog);
$stmtChangelog->bindParam(':deploymentId', $deploymentId);
$stmtChangelog->bindParam(':oldDate', $oldDate);
$stmtChangelog->bindParam(':newDate', $newDate);
$stmtChangelog->bindParam(':changeDescription', $changeDescription);

//to set using code
$datenow = new DateTime();
$formattedDate = $datenow->format('Y-m-d');

$timenow = new DateTime();
$formattedTime = $timenow->format('H:i:s');

$stmtChangelog->bindParam(':changedate', $formattedDate);
$stmtChangelog->bindParam(':changetime', $formattedTime);
$stmtChangelog->execute();

function a($depid){
    $depid1 = $depid;
    require("conn.php");
    $curr = "SELECT deployment_id, deployment_date, required_days FROM `deployment` where deployment_id = :depid"; // WHERE deployment_id not in (:deploymentId) ORDER by deployment_date";
    $stmtcur = $conn->prepare($curr);
    $stmtcur->bindParam("depid", $depid, PDO::PARAM_INT);
    $stmtcur->execute();
    $cur = $stmtcur->fetchAll(PDO::FETCH_ASSOC);
    $selectdates = "SELECT deployment_id, deployment_date FROM `deployment` WHERE deployment_id not in (:deploymentid , :depid) ORDER by deployment_date";
    $stmtseldates = $conn->prepare($selectdates);
    $stmtseldates->bindParam("deploymentid", $depid);
    $stmtseldates->bindParam("depid", $depid1);
    $stmtseldates->execute();
    $dates = $stmtseldates->fetchAll(PDO::FETCH_ASSOC);
    $datearr = [];
    $initialDate = new DateTime($cur[0]['deployment_date']);
    for ($i = 0; $i < $cur[0]['required_days']; $i++) {
        $date = clone $initialDate;  
        $date->modify('+' . $i . ' days');
        array_push($datearr, $date->format('Y-m-d')); 
    }
    foreach ($dates as $date) {
        if(in_array($date['deployment_date'], $datearr)){
            $lastElement = $datearr[count($datearr) - 1];
            $change = "UPDATE `deployment` SET `deployment_date`= :cdate WHERE deployment_id = :did";
            $stmtchange = $conn->prepare($change);
            $cdate = new DateTime($lastElement);
            $cdate->modify('+1 day');
            $changedate = $cdate->format('Y-m-d');
            $stmtchange->bindParam("cdate", $changedate);
            $stmtchange->bindParam("did", $date['deployment_id'], PDO::PARAM_INT);
            
            $sqlInsertChangelog2 = "INSERT INTO `changelog`(`deployment_id`, `old_date`, `new_date`, `change_date`, `change_time`, `info`) 
                        VALUES  (:deploymentId, :oldDate, :newDate,:changedate, :changetime, :changeDescription)";
            $stmtChangelog2 = $conn->prepare($sqlInsertChangelog2);
            $stmtChangelog2->bindParam(':deploymentId', $date['deployment_id']);
            $stmtChangelog2->bindParam(':oldDate', $date['deployment_date']);
            $stmtChangelog2->bindParam(':newDate', $changedate);
            $stmtChangelog2->bindValue(':changeDescription', 'adjusted due to deployment conflict');
            $datenow = new DateTime();
            $formattedDate = $datenow->format('Y-m-d');

            $timenow = new DateTime();
            $formattedTime = $timenow->format('H:i:s');

            $stmtChangelog2->bindParam(':changedate', $formattedDate);
            $stmtChangelog2->bindParam(':changetime', $formattedTime);
            $stmtChangelog2->execute();
            if($stmtchange->execute()){
                a($date['deployment_id']);
            }

        }else{

        }
    }
}
echo json_encode(array("response" => "Success"));
a($deploymentId);
