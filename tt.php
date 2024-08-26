<?php
session_start();
include('conn.php');

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if(isset($_POST['save_excel_data']))
{
    $fileName = $_FILES['import_file']['name'];
    $file_ext = pathinfo($fileName, PATHINFO_EXTENSION);

    $allowed_ext = ['xls','csv','xlsx'];

    if(in_array($file_ext, $allowed_ext))
    {
        $inputFileNamePath = $_FILES['import_file']['tmp_name'];
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileNamePath);
        $data = $spreadsheet->getActiveSheet()->toArray();

        $count = "0";
        foreach($data as $row)
        {
            if($count > 0)
            {
                $id = $_SESSION['userid'];
                $fullname = $row['0'];
                $email = $row['1'];
                $phone = $row['2'];
                $course = $row['3'];

                $insertqry = "INSERT INTO `portal`(`portal_owner`, `portalname`, `purl`, `version`, `pfeatures`) VALUES (:id,:a,:b,:c,:d)";
                $stmt = $conn->prepare($insertqry);
                $stmt->bindParam(":id", $fullname);
                $stmt->bindParam(":a", $id);
                $stmt->bindParam(":b", $email);
                $stmt->bindParam(":c", $phone);
                $stmt->bindParam(":d", $course);
                $stmt->execute();
                $msg = true;
            }
            else
            {
                $count = "1";
            }
        }

        if(isset($msg))
        {
            echo "success";
        }
        else
        {
            echo "not success";
        }
    }
    else
    {
        echo "smtng else";
    }
}