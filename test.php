<?php
require("conn.php");
/**
 * Handles deployment-related operations, including updating deployment dates, setting changes, and cleaning up redundant changelog entries.
 */
class Change{
    public function sett(){
        /**
         * Set a new deployment date, update the schedule change status, and log the change.
         */
        require("conn.php");
        $newDate = $_POST['new_date'];
        $oldDate  = $_POST['old_date'];
        $changeDescription  = $_POST['info'];
        $deploymentId = $_POST['deployment_id'];

        // Update the status of the schedule change
        $sql = "UPDATE schhedulechange SET change_status= :status WHERE schhedulechange.deployment_id  = :deployment_id";
        $stmt1 = $conn->prepare($sql);
        $stmt1->bindParam(":status", $_POST['status']);
        $stmt1->bindParam(":deployment_id", $_POST['deployment_id']);
        $stmt1->execute();

        // Update the deployment date
        $sqlUpdate = "UPDATE deployment SET deployment_date = :newDate WHERE deployment_id = :deploymentId";
        $stmt2 = $conn->prepare($sqlUpdate);
        $stmt2->bindParam(':newDate', $newDate);
        $stmt2->bindParam(':deploymentId', $deploymentId);
        $stmt2->execute();

        // Log the change in the changelog table
        $sqlInsertChangelog = "INSERT INTO `changelog`(`deployment_id`, `old_date`, `new_date`, `change_date`, `change_time`, `info`) VALUES  (:deploymentId, :oldDate, :newDate,:changedate, :changetime, :changeDescription)";
        $stmtChangelog = $conn->prepare($sqlInsertChangelog);
        $stmtChangelog->bindParam(':deploymentId', $deploymentId);
        $stmtChangelog->bindParam(':oldDate', $oldDate);
        $stmtChangelog->bindParam(':newDate', $newDate);
        $stmtChangelog->bindParam(':changeDescription', $changeDescription);

        // Get the current date and time for logging
        $datenow = new DateTime();
        $formattedDate = $datenow->format('Y-m-d');
        $timenow = new DateTime();
        $formattedTime = $timenow->format('H:i:s');
        $stmtChangelog->bindParam(':changedate', $formattedDate);
        $stmtChangelog->bindParam(':changetime', $formattedTime);
        $stmtChangelog->execute();
    }

    public function updateDeploymentDates($initialId) {
        /**
         * Recursively update deployment dates to resolve conflicts.
         */
        require 'conn.php';

        $queue = [$initialId]; // Queue to manage recursive updates
        $processedIds = []; // Track processed deployment IDs to avoid loops

        while(!empty($queue)){
            $depid = array_shift($queue);
            if (in_array($depid, $processedIds)) {
                continue; // Skip already processed deployment IDs
            }

            // Fetch the current deployment details
            $curr = "SELECT deployment_id, deployment_date, required_days FROM `deployment` WHERE deployment_id = :depid";
            $stmtcur = $conn->prepare($curr);
            $stmtcur->bindParam(":depid", $depid, PDO::PARAM_INT);
            $stmtcur->execute();
            $cur = $stmtcur->fetch(PDO::FETCH_ASSOC);
            if(!$cur){
                continue;
            }

            // Calculate the start and end dates of the deployment
            $startDate = new DateTime($cur['deployment_date']);
            $endDate = (clone $startDate)->modify('+' . ($cur['required_days'] - 1) . ' days');

            // Fetch other deployments to check for overlaps
            $selectdates = "SELECT deployment_id, deployment_date, required_days FROM `deployment` WHERE deployment_id <> :depid";
            $stmtseldates = $conn->prepare($selectdates);
            $stmtseldates->bindParam(":depid", $depid, PDO::PARAM_INT);
            $stmtseldates->execute();
            $dates = $stmtseldates->fetchAll(PDO::FETCH_ASSOC);

            $overlaps = false; // Flag to track if there are overlaps
            $pday  = 1; // Default padding day count for overlaps
            $weekdayarr = []; // Array to track weekdays within the deployment period

            // Calculate weekday distribution for  adjustment avoiding weekends
            $startDate2 = (clone $startDate);
            $endDate2 = (clone $endDate);
            while($startDate2 <= $endDate2 ){
                $timestamp = strtotime($startDate2->format('d-m-Y'));
                $weekDay = date('l', $timestamp);
                $weekdayarr[] = $weekDay;
                //echo $weekDay;
                $startDate2->modify('+1 day');
            }

            // Set the number of days to be added in case of weekend if 'Saturday' and 'Sunday' add 3 days if 'Saturday' or 'Sunday' add 2 days
            if(in_array('Saturday', $weekdayarr) && in_array('Sunday', $weekdayarr)){
                $pday = 3 ;
            }elseif(in_array('Saturday', $weekdayarr)){
                $pday = 2;
            }elseif(in_array('Sunday', $weekdayarr)){
                $pday = 2;
            }

            // Check for overlaps and adjust dates accordingly
            foreach ($dates as $dateRow) {
                $startDate2 = new DateTime($dateRow['deployment_date']);
                $endDate2 = (clone $startDate2)->modify('+' . ($dateRow['required_days'] - 1) . ' days');
                if ($startDate <= $endDate2 && $startDate2 <= $endDate) {
                    $overlaps = true;
                    $newStartDate = (clone $endDate)->modify('+ ' . $pday .'days');

                    $newStartDate2 = (clone $newStartDate);
                    $timestamp = strtotime($newStartDate2->format('d-m-Y'));
                    $weekDay = date('l', $timestamp);
                    if($weekDay == 'Saturday'){
                        $newStartDate2->modify('+2 days')->format('Y-m-d');
                    }elseif($weekDay == 'Sunday'){
                        $newStartDate2->modify('+1 day')->format('Y-m-d');
                    }
                    // Update the conflicting deployment's date

                    $formattedDate2 = $newStartDate2->format('Y-m-d');
                    $change = "UPDATE `deployment` SET `deployment_date` = :newDate WHERE deployment_id = :did";
                    $stmtchange = $conn->prepare($change);
                    $stmtchange->bindParam(":newDate",$formattedDate2 );
                    $stmtchange->bindParam(":did", $dateRow['deployment_id'], PDO::PARAM_INT);
                    if ($stmtchange->execute()) {
                        // Log the date change due to conflict resolution
                        $sqlInsertChangelog2 = "INSERT INTO `changelog`(`deployment_id`, `old_date`, `new_date`, `change_date`, `change_time`, `info`) VALUES  (:deploymentId, :oldDate, :newDate, CURRENT_DATE, CURRENT_TIME, :changeDescription)";
                        $stmtChangelog2 = $conn->prepare($sqlInsertChangelog2);
                        $stmtChangelog2->bindParam(':deploymentId', $dateRow['deployment_id']);
                        $stmtChangelog2->bindParam(':oldDate', $dateRow['deployment_date']);
                        $stmtChangelog2->bindParam(':newDate', $formattedDate2);
                        $stmtChangelog2->bindValue(':changeDescription', 'adjusted due to deployment conflict');
                        $stmtChangelog2->execute();
                        // Add the conflicting deployment to the queue for further checks
                        $queue[] = $dateRow['deployment_id'];
                    }
                }
            }
            $processedIds[] = $depid; // Mark the deployment ID as processed
        }
        echo json_encode(array("response" => "Deployment Dates Have Been Updated."));
    }
    
    public function delete(){
        /**
         * Clean up duplicate entries in the changelog table.
         */
        require 'conn.php';
        $qq = "DELETE t1 FROM changelog t1 INNER JOIN changelog t2 WHERE t1.deployment_id = t2.deployment_id AND t1.change_time = t2.change_time AND t1.log_id < t2.log_id;";
        $conn->query($qq);
    }

    public function mail(){
        //require 'mail.php';
        require "conn.php";
        $mailq = "SELECT changelog.log_id, changelog.new_date, changelog.old_date, changelog.info, users.email FROM `changelog` INNER JOIN deployment on deployment.deployment_id = changelog.deployment_id INNER JOIN portal on deployment.portal_id = portal.pid INNER JOIN users on users.userid = portal.portal_owner where mail = '0'";
        $stmt = $conn->query($mailq);
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($row as $ss) {
            $newDate = $ss['new_date'];
            $oldDate  = $ss['old_date'];
            $reason  = $ss['info'];
            $email = $ss['email'];
            require_once 'mail.php';
            $emailset = "update changelog set mail = 1 where log_id = :id";
            $emalread = $conn->prepare("$emailset");
            $emalread->bindParam("id", $ss['log_id']);
            $emalread->execute();
        }
        
    }
}
$deploymentId = $_POST['deployment_id'];
$obj = new Change();
if($_POST['from'] == 'adminedit'){
    $obj->updateDeploymentDates($deploymentId);
    $obj->mail();
}else if($_POST['from'] == 'adminaccept'){
    $obj->sett();
    $obj->updateDeploymentDates($deploymentId);
    $obj->delete();
    $obj->mail();
}
