<?php require_once __DIR__ . "/vendor/autoload.php";

use GO\Scheduler;

$scheduler = new Scheduler();

$scheduler->php("dailycheck.php")->daily("16:03");

$scheduler->run();
