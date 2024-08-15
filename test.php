<?php

// change deployment_date in deployment table and add to changelog table
// adjust dates of other deployments and add too changelog table 
// notify portal owmers about change

$sqlUpdate = "UPDATE deployments SET deployment_date = :newDate WHERE id = :deploymentId";
$stmt = $conn->prepare($sqlUpdate);
$stmt->bindParam(':newDate', $newDate);
$stmt->bindParam(':deploymentId', $deploymentId);

$sqlInsertChangelog = "INSERT INTO `changelog`(`deployment_id`, `old_date`, `new_date`, `change_date`, `change_time`, `info`) 
                        VALUES  (:deploymentId, :oldDate, :newDate,:changedate, changetime, :changeDescription)";
$stmtChangelog = $conn->prepare($sqlInsertChangelog);
$stmtChangelog->bindParam(':deploymentId', $deploymentId);
$stmtChangelog->bindParam(':oldDate', $oldDate);
$stmtChangelog->bindParam(':newDate', $newDate);
$stmtChangelog->bindParam(':changeDescription', $changeDescription);

//to set using code
$stmtChangelog->bindParam(':changedate', $datenow);
$stmtChangelog->bindParam(':changetime', $timenow);


function a($depid){
    require("conn.php");
    $curr = "SELECT deployment_id, deployment_date, required_days FROM `deployment` where deployment_id = :depid"; // WHERE deployment_id not in (:deploymentId) ORDER by deployment_date";
    $stmtcur = $conn->prepare($curr);
    $stmtcur->bindParam("depid", $depid, PDO::PARAM_INT);
    $stmtcur->execute();
    $cur = $stmtcur->fetchAll(PDO::FETCH_ASSOC);
    $selectdates = "SELECT deployment_id, deployment_date FROM `deployment` WHERE deployment_id not in (:deploymentid) ORDER by deployment_date";
    $stmtseldates = $conn->prepare($selectdates);
    $stmtseldates->bindParam("deploymentid", $depid);
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
            if($stmtchange->execute())
            {
                a($date['deployment_id']);
            }

        }else{

        }
    }
}

a(1);
