<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
class Deployment{
    public function checklogin(){
        session_start();
        if(isset($_SESSION['login'])) {
            $response = array(
                "response" => "logout",
                "type" => isset($_SESSION['type']) ? $_SESSION['type'] : null // Include the session 'type' if it exists
            );
        } else {
            $response = array(
                "response" => "login",
                "type" => null // Set type to null if not logged in
            );
        }
        echo json_encode($response);
    }

    public function retdate(){
        require("conn.php");
        $sql = "SELECT MIN(deployment_date) as mindate,required_days FROM `deployment`;";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($data);
            
        $conn = null;
    }

    public function logout(){
           // Start session if not already started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    session_destroy();
    $_SESSION = array();
    echo json_encode(array("response"=> "Logged Out Successfully"));
    }

    public function fetchoptions(){
        require("conn.php");
        $sql = "SELECT usertype.typeid,usertype.typename FROM usertype WHERE usertype.typeid NOT IN (100);";
        $stmt = $conn->query($sql);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $options[] = $row;
        }
        $conn = null;
        echo json_encode($options);
    }

    public function fetchportal(){
        session_start();
        require("conn.php");
        if($_GET['from'] == 'changedeployment'){
            if($_SESSION['type'] != '100'){
                $sql = "SELECT portal_id, portal.purl FROM `deployment` INNER JOIN portal on portal.pid = deployment.portal_id WHERE deployment_id NOT IN(SELECT deployment_id FROM schhedulechange)  AND portal.portal_owner = :usr;";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":usr", $_SESSION["userid"]);
            }else{
                $sql = "SELECT portal_id, portal.purl FROM `deployment` INNER JOIN portal on portal.pid = deployment.portal_id WHERE deployment_id NOT IN(SELECT deployment_id FROM schhedulechange);";
                $stmt = $conn->prepare($sql);
            }
        }else if($_GET['from'] == 'deploymentdetails'){
            if($_SESSION['type'] != '100'){
                $sql = "SELECT pid, portal.purl FROM portal WHERE pid NOT IN (SELECT deployment.portal_id from deployment) AND portal.portal_owner = :usr ";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":usr", $_SESSION["userid"]);
            }else{
                $sql = "SELECT pid, portal.purl FROM portal WHERE pid NOT IN (SELECT deployment.portal_id from deployment)";
                $stmt = $conn->prepare($sql);
            }
        }else if($_GET['from'] == 'usr'){
            $sql = "SELECT userid, username FROM users";
            $stmt = $conn->prepare($sql);
        }
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $options[] = $row;
        }
        $conn = null;
        echo json_encode($options);
    }

    public function fetchscheduledetails(){
        require("conn.php");
        $sql = "SELECT portalname,deployment_date,deployment_version,deployment_id FROM deployment INNER JOIN portal on deployment.portal_id = portal.pid where pid = :pid";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":pid", $_GET['id']);
        $stmt->execute();
        $details = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($details);
        $conn = null;
    }

    public function changepassword(){
        require("conn.php");
        $pass = $_POST['newpassword'];
        $password = password_hash($pass, PASSWORD_DEFAULT);
        $sql = "UPDATE `users` SET `password`= :password WHERE userid = :userid ";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":userid", $_SESSION['userid']);
        $stmt->bindParam(':password',$password);
        $stmt->execute();
        echo json_encode(array("response" =>"Password Changed successfully"));
        $conn = null;
    }

    public function adduser(){
        require("conn.php");
        try {
            header('Content-Type: application/json');
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $name = $_POST['name'];
                $email = $_POST['email'];
                $phone = $_POST['phone'];
                $usertype = $_POST['usertype'];
                $pass = $_POST['password'];
                $password = password_hash($pass, PASSWORD_DEFAULT);
                $sql = $conn->prepare("INSERT INTO users(username, password, email, phone, type) VALUES (:name,:password,:email,:phone,:usertype)");
                $sql->bindParam(':name',$name);
                $sql->bindParam(':email',$email);
                $sql->bindParam(':phone',$phone);
                $sql->bindParam(':password',$password);
                $sql->bindParam(':usertype',$usertype);
                $sql->execute();
                echo json_encode(array("response" =>$name." inserted successfully"));
            }
            else{
                echo json_encode(array("response" =>"No Data Recieved"));
            }
            $conn = null;
        } catch (PDOException $e) {
            echo json_encode(array("response" => "Database error: " . $e->getMessage()));
        } catch (Exception $e) {
            echo json_encode(array("response" => "General error: " . $e->getMessage()));
        } finally {
            $conn = null;
        }
    }

    public function login() {
        require("conn.php");
        try {
            header('Content-Type: application/json');
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $email = $_POST['email'];
                $pass = $_POST['password'];
                $sql = $conn->prepare("SELECT * FROM users WHERE email = :email");
                $sql->bindParam(":email",$email);
                $sql->execute();
                $user = $sql->fetch();
                if ($user && password_verify($pass, $user['password'])) {
                    session_start();
                    $_SESSION['login'] = true;
                    $_SESSION['email'] = $email;
                    $_SESSION['type'] = $user['type'];
                    $_SESSION['userid'] = $user['userid'];
                    $welcome = "Welcome ".$user['username'];
                    echo json_encode(array("response" =>$welcome));
                } else {
                    echo json_encode(array("response" =>"Email or Password Incorrect "));
                }
            }
            else{
                echo json_encode(array("response" =>"No Data Recieved"));
            }
            $conn = null;
        } catch (PDOException $e) {
            echo json_encode(array("response" => "Database error: " . $e->getMessage()));
        } catch (Exception $e) {
            echo json_encode(array("response" => "General error: " . $e->getMessage()));
        } finally {
            $conn = null;
        }
    }


    public function addportal(){
        require("conn.php");
        session_start();
        try {
            if(isset($_SESSION['login'])){
                header('Content-Type: application/json');
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $portal_name = $_POST['portal_name'];
                    $portal_url = $_POST['portal_url'];
                    $portal_version = $_POST['portal_version'];
                    $portal_features = $_POST['portal_features'];
                    $sql = $conn->prepare("INSERT INTO portal(portal_owner,portalname, purl, version, pfeatures) VALUES (:owner,:portal_name,:portal_url,:portal_version,:portal_features)");
                    $sql->bindParam(':owner',$_SESSION['userid']);
                    $sql->bindParam(':portal_name',$portal_name);
                    $sql->bindParam(':portal_url',$portal_url);
                    $sql->bindParam(':portal_version',$portal_version);
                    $sql->bindParam(':portal_features',$portal_features);
                    $sql->execute();
                    echo json_encode(array("response" =>$portal_name." inserted successfully"));
                }else{
                    echo json_encode(array("response" =>"No Data Recieved"));
                }
            }else{
                echo json_encode(array("response"=> "Login Error"));
            }
            
            $conn = null;
        } catch (PDOException $e) {
            echo json_encode(array("response" => "Database error: " . $e->getMessage()));
        } catch (Exception $e) {
            echo json_encode(array("response" => "General error: " . $e->getMessage()));
        } finally {
            $conn = null;
        }
    }

    public function adddeployment(){
        require("conn.php");
        try {
            header('Content-Type: application/json');
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $portal_url = $_POST['portal_url'];
                $portal_version = $_POST['portal_version'];
                $deployment_date = $_POST['deployment_date'];
                $num_days = $_POST['num_days'];
                $deployment_note = $_POST['deployment_note'];
                if (isset($_FILES['deployment_plan']) && $_FILES['deployment_plan']['error'] == 0) {
                    $uploadDir = 'uploads/'; 
                    $uploadFile = $uploadDir . basename($_FILES['deployment_plan']['name']);
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    if (move_uploaded_file($_FILES['deployment_plan']['tmp_name'], $uploadFile)) {
                            $sql = $conn->prepare("INSERT INTO deployment(portal_id, deployment_version, deployment_date, deployment_note, deployment_plan, required_days) VALUES (:portal_url,:portal_version,:deployment_date,:deployment_note, :deployment_plan, :num_days)");
                            $sql->bindParam(':portal_url',$portal_url);
                            $sql->bindParam(':portal_version',$portal_version);
                            $sql->bindParam(':deployment_date',$deployment_date);
                            $sql->bindParam(':num_days',$num_days);
                            $sql->bindParam(':deployment_note',$deployment_note);
                            $sql->bindParam(':deployment_plan',$uploadFile);
                            $sql->execute();
                            echo json_encode(array("response" =>"Deployment Details added successfully"));
                    } else {
                        echo json_encode(array("response"=>"file error"));
                    }
                }
            }
            else{
                echo json_encode(array("response" =>"No Data Recieved"));
            }
            $conn = null;
        } catch (PDOException $e) {
            echo json_encode(array("response" => "Database error: " . $e->getMessage()));
        } catch (Exception $e) {
            echo json_encode(array("response" => "General error: " . $e->getMessage()));
        } finally {
            $conn = null;
        }
    }

    public function changeschedule() {
        session_start();
        require("conn.php");
        try {
            header('Content-Type: application/json');
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $deployment_id = $_POST['deployment_id'];
                $new_date = $_POST['new_date'];
                $change_note = $_POST['change_note'];
                $userid = $_SESSION['userid'];
                $sql = $conn->prepare("INSERT INTO schhedulechange(deployment_id, new_date, user_id, user_note) VALUES (:deployment_id,:new_date,:userid,:change_note)");
                $sql->bindParam(':deployment_id',$deployment_id);
                $sql->bindParam(':new_date',$new_date);
                $sql->bindParam(':userid',$userid);
                $sql->bindParam(':change_note',$change_note);
                $sql->execute();
                echo json_encode(array("response" =>"Schedule change added successfully"));
            }
            else{
                echo json_encode(array("response" =>"No Data Recieved"));
            }
            $conn = null;
        } catch (PDOException $e) {
            echo json_encode(array("response" => "Database error: " . $e->getMessage()));
        } catch (Exception $e) {
            echo json_encode(array("response" => "General error: " . $e->getMessage()));
        } finally {
            $conn = null;
        }
    }


    public function datatable(){
        require('conn.php');
        try {
            $sql = "SELECT portal.purl, portal.portalname,deployment.deployment_date, users.username, schhedulechange.new_date FROM `deployment` INNER JOIN portal ON deployment.portal_id = portal.pid INNER JOIN schhedulechange on schhedulechange.deployment_id = deployment.deployment_id INNER JOIN users on schhedulechange.user_id = users.userid WHERE schhedulechange.change_status = 'Pending' ;";
        
            $stmt = $conn->prepare($sql);
            $stmt->execute();
        
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
            if (!empty($data)) {
                header('Content-Type: application/json');
                echo json_encode($data);
            } else {
                echo json_encode(array("message" => "No data found"));
            }
        
        } catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
        $conn = null;
    }

    public function deploymentstable(){
        require('conn.php');
        try {
            $sql = "SELECT portal.purl, portal.portalname,deployment.deployment_date, users.username, deployment.required_days , deployment.deployment_id FROM `deployment` INNER JOIN portal ON deployment.portal_id = portal.pid INNER JOIN users on portal.portal_owner = users.userid order by deployment_date";
        
            $stmt = $conn->prepare($sql);
            $stmt->execute();
        
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
            if (!empty($data)) {
                header('Content-Type: application/json');
                echo json_encode($data);
            } else {
                echo json_encode(array("message" => "No data found"));
            }
        
        } catch(PDOException $e) {
            echo json_encode(array("message" =>"Connection failed: " . $e->getMessage()));
        }
        $conn = null;
    }


    public function viewdetails(){
        require("conn.php");
        if($_GET['from'] == 'schedule'){
            $sql = "SELECT deployment_version,deployment_date, deployment_note, required_days,portalname, purl, version, pfeatures, new_date, user_note, username, deployment_plan, schhedulechange.deployment_id FROM `deployment` INNER JOIN portal ON deployment.portal_id = portal.pid INNER JOIN schhedulechange on schhedulechange.deployment_id = deployment.deployment_id INNER JOIN users on schhedulechange.user_id = users.userid where purl = :purl";
        }elseif($_GET["from"] == "deployment"){
            $sql = "SELECT deployment_version,deployment_date, deployment_note, required_days,portalname, purl, version, pfeatures, username, deployment_plan, portal_id FROM `deployment` INNER JOIN portal ON deployment.portal_id = portal.pid INNER JOIN users on portal.portal_owner = users.userid where purl = :purl ";
        }
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":purl", $_GET['purl']);
        $stmt->execute();
        $details = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($details);
        $conn = null;
    }

    public function managechange(){
        require("conn.php");
        header('Content-Type: application/json');
        $sql = "UPDATE schhedulechange SET change_status= :status WHERE schhedulechange.deployment_id  = :deployment_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":status", $_POST['status']);
        $stmt->bindParam(":deployment_id", $_POST['deployment_id']);
        $stmt->execute();
        echo json_encode(array("response" => "Success"));
        $conn = null;
    }

    public function disabledate(){
        require("conn.php");
        $sql = "SELECT deployment_date, required_days FROM `deployment`;";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);  
        $allDates = [];  
        foreach ($data as $row) {
            $initialDate = new DateTime($row['deployment_date']);
            $datearr = [];
            for ($i = 0; $i < $row['required_days']; $i++) {
                $date = clone $initialDate;  
                $date->modify('+' . $i . ' days');
                array_push($datearr, $date->format('Y-m-d')); 
            }
            $allDates = array_merge($allDates, $datearr);  
        }
        echo json_encode($allDates);  

    }

    public function message(){
        require("conn.php");
        session_start();
        $stmt = $conn->prepare("select changelog.old_date,changelog.new_date,changelog.change_date,changelog.change_time,changelog.info, portal.portalname,portal.purl,users.username FROM `changelog` INNER JOIN deployment on changelog.deployment_id = deployment.deployment_id INNER JOIN portal on portal.pid =deployment.portal_id INNER join  users on portal.portal_owner = users.userid where users.userid = :id");
        $stmt->bindParam(":id", $_SESSION['userid']);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($res);
    }

    public function editdeployment(){
        require("conn.php");
        session_start();
        $stmt = $conn->prepare("UPDATE `deployment` SET `deployment_version`=:dversion,`deployment_date`=:ddate,`required_days`=:rdays,`deployment_note`=:dnote WHERE deployment.portal_id = :pid");
        $stmt->bindParam(":dversion", $_POST['deployment_version']);
        $stmt->bindParam(":ddate", $_POST['deployment_date']);
        $stmt->bindParam(":rdays", $_POST['days']);
        $stmt->bindParam(":dnote", $_POST['new_features']);
        $stmt->bindParam(":pid", $_POST['portal_id']);
        $stmt->execute();
        $stmt2 = $conn->prepare("UPDATE `portal` SET `portalname`=:pname ,`version`=:pversion,`pfeatures`=:pfeatures WHERE portal.pid  = :pid");
        $stmt2->bindParam(":pname", $_POST['portal_name']);
        $stmt2->bindParam(":pversion", $_POST['current_version']);
        $stmt2->bindParam(":pfeatures", $_POST['portal_features']);
        $stmt2->bindParam(":pid", $_POST['portal_id']);
        $stmt2->execute();
        echo json_encode(array("message" => "Data Updated"));
    }

}

try{
    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(isset($_POST['function'])){
        $obj = new Deployment();
        call_user_func(array($obj, $_POST['function']));
    }}
    else if($_SERVER['REQUEST_METHOD'] === 'GET'){
        if(isset($_GET['function'])){
            $obj = new Deployment();
            call_user_func(array($obj,$_GET['function']));
        }
    }    
    }catch(Exception $e){
        echo json_encode(array(''=> $e->getMessage()));
    }