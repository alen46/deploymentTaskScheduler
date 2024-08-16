<?php
require "conn.php";
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;



$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set the headers
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
foreach ($headers as $cell => $header) {
    $sheet->setCellValue($cell, $header);
}

$query = $conn->query("SELECT deployment_version as DeploymentVersion,deployment_date as DeploymentDate, deployment_note as DeploymentNote, required_days as RequiredDays,portalname as PortalName, purl as PortalURL, version as CurrentPortalVersion, pfeatures as PortalFeatures, username as PortalOwner FROM `deployment` INNER JOIN portal ON deployment.portal_id = portal.pid INNER JOIN users on portal.portal_owner = users.userid");
$rowNumber = 2; // Start inserting data at row 2

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

// Write the file
$writer = new Xlsx($spreadsheet);
$writer->save('reports/deployment_data.xlsx');

echo "Excel file created successfully: deployment_data.xlsx";

// Close the database connection
$pdo = null;
?>
