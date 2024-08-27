<?php
session_start();
include('conn.php');

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
                $name = $row['0'];
                $url = $row['1'];
                $version = $row['2'];
                $features = $row['3'];

                $insertqry = "INSERT INTO `portal`(`portal_owner`, `portalname`, `purl`, `version`, `pfeatures`) VALUES (:id,:a,:b,:c,:d)";
                $stmt = $conn->prepare($insertqry);
                $stmt->bindParam(":id", $id);
                $stmt->bindParam(":a", $name);
                $stmt->bindParam(":b", $url);
                $stmt->bindParam(":c", $version);
                $stmt->bindParam(":d", $features);
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
            echo json_encode(array("status"=> "success","msg"=>'Successfully Inserted'));
        }
        else
        {
            echo json_encode(array('status'=> 'Failed','msg'=> 'Please Try Again'));
        }
    }
    else
    {
       echo json_encode(array('status'=> 'Something','msg'=> 'Some other error'));
    }