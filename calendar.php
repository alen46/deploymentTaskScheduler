<?php
    require_once "conn.php";

    $json = array();
    $sqlQuery = "SELECT deployment.deployment_id as id, portal.portalname as title, DATE_FORMAT(deployment.deployment_date,'%Y-%m-%dT%H:%i:%s') as start, DATE_FORMAT(date_add(deployment.deployment_date, INTERVAL deployment.required_days day),'%Y-%m-%dT%H:%i:%s') as end from deployment INNER JOIN portal on deployment.portal_id = portal.pid order by deployment_date asc;";
    
    try {
        // Assuming $pdo is your PDO connection object
        $stmt = $conn->prepare($sqlQuery);
        $stmt->execute();
    
        $eventArray = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach( $eventArray as &$event ){
            $weekdayarr = [];
            $stdate = new DateTime($event['start']);
            $enddate = new DateTime($event['end']);
            while($stdate <= $enddate ){
                $timestamp = strtotime($stdate->format('d-m-Y'));
                $weekDay = date('l', $timestamp);
                $weekdayarr[] = $weekDay;
                $stdate->modify('+1 day');
            }
            // echo $weekDay;
            // Set the number of days to be added in case of weekend if 'Saturday' and 'Sunday' add 2 days
            $pday = 0;
            if(in_array('Saturday', $weekdayarr) && in_array('Sunday', $weekdayarr)){
                $pday = 2 ;
            }
            $enddate->modify('+ ' . $pday .'days');
            $event['end']  = $enddate->format('Y-m-d\TH:i:s');
        }
        echo json_encode($eventArray);
    } catch (PDOException $e) {
        // Handle any errors
        echo json_encode(['error' => $e->getMessage()]);
    }
    
?>