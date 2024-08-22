<?php
	// $startDate = "2024-08-2";
	// // $endDate = "08-01-2018";
	// $startDate = new DateTime($startDate);
	// $endDate = new DateTime($endDate);
    // $weekdayarr = [];
    $datearr = ["2024-08-23", "2024-08-24", "2024-08-25","2024-08-26", "2024-08-27", "2024-08-28"];
    $x = 0;
    foreach($datearr as $startDate){
        $startDate = new DateTime($startDate);
        if(date('l',strtotime($startDate->format('Y-m-d')))== 'Saturday' || date('l',strtotime($startDate->format('Y-m-d'))) == 'Sunday'){
            $x++;
        }
    }
    $y = new DateTime($datearr[sizeof($datearr) - 1]);
    for($i = 1;$i<$x + 1 ;$i++){
        $y->modify("+".$i." days");
        array_push($datearr, $y->format('Y-m-d'));
    }
    
    // elseif(date('l',strtotime($startDate->format('Y-m-d'))) == 'Sunday'){
    //     echo $startDate->format('Y-m-d');
    //     echo date('l',strtotime($startDate->format('Y-m-d')));
    //     echo '<br><br>gg<br><br>';
    //     echo $startDate->modify('+ 1 days')->format('Y-m-d');
    //     echo date('l',strtotime($startDate->format('Y-m-d')));
    //     echo 'dd';
    // }


	// while($startDate <= $endDate ){
	// 	$timestamp = strtotime($startDate->format('d-m-Y'));
	// 	$weekDay = date('l', $timestamp);
    //     $weekdayarr[] = $weekDay;
    //     echo $weekDay;
	// 	$startDate->modify('+1 day');
	// }
    // if(in_array('Saturday', $weekdayarr) && in_array('Sunday', $weekdayarr)){
    //     echo '<br>111';
    // }elseif(in_array('Saturday', $weekdayarr)){
    //     echo '<br>222';
    // }elseif(in_array('Sunday', $weekdayarr)){
    //     echo '<br>333';
    // }
?>
