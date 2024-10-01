<?php
require "conn.php";
require 'vendor/autoload.php';

// PhpSpreadsheet library to handle Excel file generation
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


// Initialize a new spreadsheet and select the active sheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Check the type of report to generate based on the POST request
if($_POST['type'] == 'change'){

    // Set the title for the sheet
    $sheet->setTitle('All Changes');

    // Define column headers for the change log report
    $headers = [
        'A1' => 'Portal URL',
        'B1' => 'Portal Name',
        'C1' => 'Portal Owner',
        'D1' => 'Old Date',
        'E1' => 'New Date',
        'F1' => 'Change Date',
        'G1' => 'Change Time',
        'H1' => 'Change Info'
    ];

    // Set the column headers in the sheet
    foreach ($headers as $cell => $header) {
        $sheet->setCellValue($cell, $header);
    }
    $query = $conn->query("SELECT users.username PortalOwner, portal.portalname as PortalName, portal.purl PortalURL, changelog.old_date as OldDate, changelog.new_date as NewDate, changelog.change_date as ChangeDate, changelog.change_time as ChangeTime, changelog.info AS ChangeInfo FROM `changelog` INNER JOIN deployment ON changelog.deployment_id = deployment.deployment_id INNER JOIN portal on portal.pid = deployment.portal_id INNER JOIN users on portal.portal_owner = users.userid ORDER BY change_date;");
    $rowNumber = 2; 

    // Add data to sheet from the query result
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $sheet->setCellValue('A' . $rowNumber, $row['PortalURL']);
        $sheet->setCellValue('B' . $rowNumber, $row['PortalName']);
        $sheet->setCellValue('C' . $rowNumber, $row['PortalOwner']);
        $sheet->setCellValue('D' . $rowNumber, $row['OldDate']);
        $sheet->setCellValue('E' . $rowNumber, $row['NewDate']);
        $sheet->setCellValue('F' . $rowNumber, $row['ChangeDate']);
        $sheet->setCellValue('G' . $rowNumber, $row['ChangeTime']);
        $sheet->setCellValue('H' . $rowNumber, $row['ChangeInfo']);
        $rowNumber++;
    }
}
elseif($_POST['type'] == 'olddeployments'){

    // Set the title for the sheet
    $sheet->setTitle('Previous Deployments');

    // Define column headers for the change log report
    $headers = [
        'A1' => 'Portal URL',
        'B1' => 'Portal Name',
        'C1' => 'Portal Owner',
        'D1' => 'Deployment Date',
        'E1' => 'Old Version',
        'F1' => 'New Version',
        'G1' => 'Old Features',
        'H1' => 'New Features'
    ];

    // Set the column headers in the sheet
    foreach ($headers as $cell => $header) {
        $sheet->setCellValue($cell, $header);
    }
    $query = $conn->query("SELECT users.username PortalOwner, portal.portalname as PortalName, portal.purl PortalURL, deployment_log.date as ddate, deployment_log.oldversion as oldversion, portal.version as newversion, deployment_log.oldfeatures as oldfeatures, portal.pfeatures AS newfeatures FROM `deployment_log` INNER JOIN portal on portal.pid = deployment_log.portal_id INNER JOIN users on portal.portal_owner = users.userid ORDER BY deployment_log.date;");
    $rowNumber = 2; 

    // Add data to sheet from the query result
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $sheet->setCellValue('A' . $rowNumber, $row['PortalURL']);
        $sheet->setCellValue('B' . $rowNumber, $row['PortalName']);
        $sheet->setCellValue('C' . $rowNumber, $row['PortalOwner']);
        $sheet->setCellValue('D' . $rowNumber, $row['ddate']);
        $sheet->setCellValue('E' . $rowNumber, $row['oldversion']);
        $sheet->setCellValue('F' . $rowNumber, $row['newversion']);
        $sheet->setCellValue('G' . $rowNumber, $row['oldfeatures']);
        $sheet->setCellValue('H' . $rowNumber, $row['newfeatures']);
        $rowNumber++;
    }
}
else{

    // Define column headers for the change log report
    $headers = [
        'A1' => 'Portal URL',
        'B1' => 'Portal Name',
        'C1' => 'Portal Owner',
        'D1' => 'Current Portal Version',
        'E1' => 'Deployment Version',
        'F1' => 'Deployment Date',
        'G1' => 'Required Days',
        'H1' => 'Portal Features',
        'I1' => 'New Features'
    ];

    // Set the column headers in the sheet
    foreach ($headers as $cell => $header) {
        $sheet->setCellValue($cell, $header);
    }

    if($_POST['type'] == 'date'){
        $from = $_POST['from'];
        $to = $_POST['to'];
        $sheet->setTitle('Date - '. $from .'--'. $to);
        $query = $conn->prepare("SELECT deployment_version AS DeploymentVersion, deployment_date AS DeploymentDate, deployment_note AS DeploymentNote, required_days AS RequiredDays, portalname AS PortalName, purl AS PortalURL, version AS CurrentPortalVersion, pfeatures AS PortalFeatures, username AS PortalOwner FROM deployment INNER JOIN portal ON deployment.portal_id = portal.pid INNER JOIN users ON portal.portal_owner = users.userid WHERE deployment_date BETWEEN :from AND :to ORDER BY `DeploymentDate` DESC;");
        $query->bindParam(':from', $from);
        $query->bindParam(':to', $to);
        $query->execute();
    }
    else if($_POST["type"] == "alldeployments"){

        // Set the title for the sheet
        $sheet->setTitle('All Deployments');
        $query = $conn->query("SELECT deployment_version as DeploymentVersion,deployment_date as DeploymentDate, deployment_note as DeploymentNote, required_days as RequiredDays,portalname as PortalName, purl as PortalURL, version as CurrentPortalVersion, pfeatures as PortalFeatures, username as PortalOwner FROM `deployment` INNER JOIN portal ON deployment.portal_id = portal.pid INNER JOIN users on portal.portal_owner = users.userid");
    }
    elseif($_POST['type'] == 'user'){
        $usr = $_POST['usr'];

        // Set the title for the sheet
        $sheet->setTitle('User - '.$usr);
        if( $usr == ''){
            $query = $conn->prepare("SELECT deployment_version AS DeploymentVersion, deployment_date AS DeploymentDate, deployment_note AS DeploymentNote, required_days AS RequiredDays, portalname AS PortalName, purl AS PortalURL, version AS CurrentPortalVersion, pfeatures AS PortalFeatures, username AS PortalOwner FROM deployment INNER JOIN portal ON deployment.portal_id = portal.pid INNER JOIN users ON portal.portal_owner = users.userid WHERE users.userid in (SELECT users.userid from users)");
        }else{
            $query = $conn->prepare("SELECT deployment_version AS DeploymentVersion, deployment_date AS DeploymentDate, deployment_note AS DeploymentNote, required_days AS RequiredDays, portalname AS PortalName, purl AS PortalURL, version AS CurrentPortalVersion, pfeatures AS PortalFeatures, username AS PortalOwner FROM deployment INNER JOIN portal ON deployment.portal_id = portal.pid INNER JOIN users ON portal.portal_owner = users.userid WHERE users.userid = :usr");
            $query->bindParam(':usr', $usr);
        }        
        $query->execute();
    }
    elseif($_POST['type'] == 'portal'){
        $portl = $_POST['portl'];

        // Set the title for the sheet
        $sheet->setTitle('Portal - '. $portl);
        if( $portl == ''){
            $query = $conn->prepare("SELECT deployment_version AS DeploymentVersion, deployment_date AS DeploymentDate, deployment_note AS DeploymentNote, required_days AS RequiredDays, portalname AS PortalName, purl AS PortalURL, version AS CurrentPortalVersion, pfeatures AS PortalFeatures, username AS PortalOwner FROM deployment INNER JOIN portal ON deployment.portal_id = portal.pid INNER JOIN users ON portal.portal_owner = users.userid WHERE portal.pid in (SELECT portal.pid from portal)");
        }else{
            $query = $conn->prepare("SELECT deployment_version AS DeploymentVersion, deployment_date AS DeploymentDate, deployment_note AS DeploymentNote, required_days AS RequiredDays, portalname AS PortalName, purl AS PortalURL, version AS CurrentPortalVersion, pfeatures AS PortalFeatures, username AS PortalOwner FROM deployment INNER JOIN portal ON deployment.portal_id = portal.pid INNER JOIN users ON portal.portal_owner = users.userid WHERE portal.pid = :portl");
            $query->bindParam(':portl', $portl);
        }        
        $query->execute();
    }
    $rowNumber = 2;

    // Add data to sheet from the query result
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $sheet->setCellValue('A' . $rowNumber, $row['PortalURL']);
        $sheet->setCellValue('B' . $rowNumber, $row['PortalName']);
        $sheet->setCellValue('C' . $rowNumber, $row['PortalOwner']);
        $sheet->setCellValue('D' . $rowNumber, $row['CurrentPortalVersion']);
        $sheet->setCellValue('E' . $rowNumber, $row['DeploymentVersion']);
        $sheet->setCellValue('F' . $rowNumber, $row['DeploymentDate']);
        $sheet->setCellValue('G' . $rowNumber, $row['RequiredDays']);
        $sheet->setCellValue('H' . $rowNumber, $row['PortalFeatures']);
        $sheet->setCellValue('I' . $rowNumber, $row['DeploymentNote']);
        $rowNumber++;
    }
}

// Set autosize 
foreach (range('A', 'I') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$writer = new Xlsx($spreadsheet);
$datenow = new DateTime();
$formattedDate = $datenow->format('Y_m_d');
$timenow = new DateTime();
$formattedTime = $timenow->format('H_i_s');

// Set the filename for the output Excel file
$filename =htmlspecialchars( 'reports/report'.$formattedDate.'__'.$formattedTime.'.xlsx');

include_once 'main.php';
try{
    $obj = new Deployment();
    $obj->useractionlog('report generation');
}catch(Exception $e){
    echo json_encode(array(''=> $e->getMessage()));
}

// Save the generated Excel file
$writer->save($filename);
echo json_encode(array('file'=> $filename));
$conn = null;
?>
