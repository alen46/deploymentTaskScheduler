<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
class Deployment{
    public function checklogin(){
        session_start();
        if(isset($_SESSION['login'])) {
            echo json_encode(array("response" =>"logout"));
        }else{
            echo json_encode(array("response"=> "login"));
        }
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
        require("conn.php");
        if($_GET['from'] == 'changedeployment'){
            $sql = "SELECT portal_id, portal.purl FROM `deployment` INNER JOIN portal on portal.pid = deployment.portal_id WHERE deployment_id NOT IN(SELECT deployment_id FROM schhedulechange);";
        }else if($_GET['from'] == 'deploymentdetails'){
            $sql = "SELECT pid, portal.purl FROM portal WHERE pid NOT IN (SELECT deployment.portal_id from deployment)";
        }
        $stmt = $conn->query($sql);
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
        try {
            header('Content-Type: application/json');
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $portal_name = $_POST['portal_name'];
                $portal_url = $_POST['portal_url'];
                $portal_version = $_POST['portal_version'];
                $portal_features = $_POST['portal_features'];
                $sql = $conn->prepare("INSERT INTO portal(portalname, purl, version, pfeatures) VALUES (:portal_name,:portal_url,:portal_version,:portal_features)");
                $sql->bindParam(':portal_name',$portal_name);
                $sql->bindParam(':portal_url',$portal_url);
                $sql->bindParam(':portal_version',$portal_version);
                $sql->bindParam(':portal_features',$portal_features);
                $sql->execute();
                echo json_encode(array("response" =>$portal_name." inserted successfully"));
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
            $sql = "SELECT portal.purl, portal.portalname,deployment.deployment_date, users.username, schhedulechange.new_date FROM `deployment` INNER JOIN portal ON deployment.portal_id = portal.pid INNER JOIN schhedulechange on schhedulechange.deployment_id = deployment.deployment_id INNER JOIN users on schhedulechange.user_id = users.userid;";
        
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


    public function viewschedulechange(){
        require("conn.php");
        $sql = "SELECT deployment_version,deployment_date, deployment_note, required_days,portalname, purl, version, pfeatures, new_date, user_note, username, deployment_plan, schhedulechange.deployment_id FROM `deployment` INNER JOIN portal ON deployment.portal_id = portal.pid INNER JOIN schhedulechange on schhedulechange.deployment_id = deployment.deployment_id INNER JOIN users on schhedulechange.user_id = users.userid where purl = :purl";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":purl", $_GET['purl']);
        $stmt->execute();
        $details = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($details);
        $conn = null;
    }

    public function managechange(){
        require("conn.php");
        $sql = "UPDATE schhedulechange SET change_status= :status WHERE schhedulechange.deployment_id  :deployment_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":status", $_POST['status']);
        $stmt->bindParam(":deployment_id", $_POST['deployment_id']);
        $stmt->execute();
        echo json_encode(array("response" => "Success"));
        $conn = null;
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