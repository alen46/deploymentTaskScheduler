<?php
require("conn.php");
class Change{
    public function sett(){
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
    }

    public function updateDeploymentDates($initialId) {
        require 'conn.php';
        $queue = [$initialId];
        $processedIds = [];
        while(!empty($queue)){
            $depid = array_shift($queue);
            if (in_array($depid, $processedIds)) {
                continue;
            }
            $curr = "SELECT deployment_id, deployment_date, required_days FROM `deployment` WHERE deployment_id = :depid";
            $stmtcur = $conn->prepare($curr);
            $stmtcur->bindParam(":depid", $depid, PDO::PARAM_INT);
            $stmtcur->execute();
            $cur = $stmtcur->fetch(PDO::FETCH_ASSOC);
            if(!$cur){
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
            $pday  = 1;
            $weekdayarr = [];
            $startDate2 = (clone $startDate);
            $endDate2 = (clone $endDate);
            while($startDate2 <= $endDate2 ){
                $timestamp = strtotime($startDate2->format('d-m-Y'));
                $weekDay = date('l', $timestamp);
                $weekdayarr[] = $weekDay;
                //echo $weekDay;
                $startDate2->modify('+1 day');
            }
            if(in_array('Saturday', $weekdayarr) && in_array('Sunday', $weekdayarr)){
                $pday = 3 ;
            }elseif(in_array('Saturday', $weekdayarr)){
                $pday = 2;
            }elseif(in_array('Sunday', $weekdayarr)){
                $pday = 2;
            }
            foreach ($dates as $dateRow) {
                $startDate2 = new DateTime($dateRow['deployment_date']);
                $endDate2 = (clone $startDate2)->modify('+' . ($dateRow['required_days'] - 1) . ' days');
                if ($startDate <= $endDate2 && $startDate2 <= $endDate) {
                    $overlaps = true;
                    $newStartDate = (clone $endDate)->modify('+ ' . $pday .'days')->format('Y-m-d');
                    // echo $pday;
                    // $newdate2 = (clone $endDate);
                    // if(date('l',strtotime($newdate2->format('Y-m-d'))) == 'Saturday'){
                    //     echo $newdate2->format('Y-m-d');
                    //     echo '<br><br>ff<br><br>';
                    //     echo $newdate2->modify('+ 0 day')->format('Y-m-d');
                    //     echo date('l',strtotime($newdate2->format('Y-m-d')));
                    //     echo 'dd';
                    // }elseif(date('l',strtotime($newdate2->format('Y-m-d')) == 'Sunday')){
                    //     echo $newdate2->format('Y-m-d');
                    //     echo '<br><br>gg<br><br>';
                    //     echo $newdate2->modify('+ 1 days')->format('Y-m-d');
                    //     echo date('l',strtotime($newdate2->format('Y-m-d')));
                    //     echo 'dd';
                    // }
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
        echo json_encode(array("response" => "Deployment Dates Have Been Updated."));
    }
    
    public function delete(){
        require 'conn.php';
        $qq = "DELETE t1 FROM changelog t1 INNER JOIN changelog t2 WHERE t1.deployment_id = t2.deployment_id AND t1.change_time = t2.change_time AND t1.log_id < t2.log_id;";
        $conn->query($qq);
    }
}
$deploymentId = $_POST['deployment_id'];
$obj = new Change();
if($_POST['from'] == 'adminedit'){
    $obj->updateDeploymentDates($deploymentId);
}else if($_POST['from'] == 'adminaccept'){
    $obj->sett();
    $obj->updateDeploymentDates($deploymentId);
    $obj->delete();
}
