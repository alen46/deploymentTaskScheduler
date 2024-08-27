<?php
$newStartDate2 = new DateTime('2024-08-25');

$timestamp = strtotime($newStartDate2->format('d-m-Y'));
$weekDay = date('l', $timestamp);
echo $weekDay;
if($weekDay == 'Saturday'){
    $newStartDate2->modify('+2 days');
    echo $newStartDate2->format('Y-m-d');
    $timestamp = strtotime($newStartDate2->format('d-m-Y'));
    $weekDay = date('l', $timestamp);
    echo $weekDay;
}elseif($weekDay == 'Sunday'){
    $newStartDate2->modify('+1 day');
    echo $newStartDate2->format('Y-m-d');
    $timestamp = strtotime($newStartDate2->format('d-m-Y'));
    $weekDay = date('l', $timestamp);
    echo $weekDay;
}
