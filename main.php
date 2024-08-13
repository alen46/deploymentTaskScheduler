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
                echo json_encode(array("response" =>"$name inserted successfully"));
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
                    $welcome = "Welcome ".$user['username'];
                    echo json_encode(array("response" =>$welcome));
                } else {
                    echo json_encode(array("response" =>"Email or Password Incorrect"));
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