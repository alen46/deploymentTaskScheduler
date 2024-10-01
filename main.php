<?php 
//Admin@123
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
class Deployment{
    public function checklogin(): void{
        /**
         * check whether the user is logged in
         * 
         * Starts the session and verifies if the `login` session variable is set.
         * Returns a JSON response indicating the user's login status and type.
         */
        session_start();
        try{
            //check if $_SESSION['login'] variable is set for checking whether user is logged in
            if(isset($_SESSION['login'])) {
                $response = array(
                    "response" => "logout",
                    "type" => isset($_SESSION['type']) ? $_SESSION['type'] : null 
                );
            } else {
                $response = array(
                    "response" => "login",
                    "type" => null 
                );
            }
            echo json_encode($response);
        }catch (PDOException $e) {
            echo json_encode(array("response" => "Database error: " . $e->getMessage()));
        } catch (Exception $e) {
            echo json_encode(array("response" => "General error: " . $e->getMessage()));
        }finally{
            $conn = null;
        }
    }

    public function retdate(){
        /**
         * Return the oldest deployment date from table for checking
         */
        require("conn.php");
        try{
            $sql = "SELECT MIN(deployment_date) as mindate,required_days FROM `deployment`;";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($data);
        }catch (PDOException $e) {
            echo json_encode(array("response" => "Database error: " . $e->getMessage()));
        } catch (Exception $e) {
            echo json_encode(array("response" => "General error: " . $e->getMessage()));
        }finally{
            $conn = null;
        }
    }

    public function logout(){
        /**
         * Logout the user and destroy session
         */
        try{
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            self::useractionlog(fun: 'logout');
            session_destroy();
            $_SESSION = array();
            echo json_encode(array("response"=> "Logged Out Successfully"));
        }catch (Exception $e) {
            echo json_encode(array("response" => "General error: " . $e->getMessage()));
        }
    }

    public function fetchoptions(){
        /**
         * Return the diffrent user types for select tag
         */
        require("conn.php");
        try{
            $sql = "SELECT usertype.typeid,usertype.typename FROM usertype WHERE usertype.typeid NOT IN (100);";
            $stmt = $conn->query($sql);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $options[] = $row;
            }
            echo json_encode($options);
        }catch (PDOException $e) {
            echo json_encode(array("response" => "Database error: " . $e->getMessage()));
        } catch (Exception $e) {
            echo json_encode(array("response" => "General error: " . $e->getMessage()));
        }finally{
            $conn = null;
        }
    }

    public function fetchportal(){
        /**
         * Return  Fetch portal and portal URL for a select tag based on different conditions
         * 
         * It uses session variables to determine the user's type and executes different SQL queries accordingly.
         * 
         * 'changedeployment': returns portal and URL to the changedeployment page
         * 'deploymentdetails': returns portal and URL to the deployment details page
         * 'portl': returns portal and URL to the edit portal page
         * 'usr': returns details of all users except admin
         */
        session_start();
        require("conn.php");
        try{
            if($_SERVER['REQUEST_METHOD'] === 'GET'){
                if($_GET['from'] == 'changedeployment'){
                    if($_SESSION['type'] != '100'){
                        $sql = "SELECT portal_id, portal.purl FROM `deployment` INNER JOIN portal on portal.pid = deployment.portal_id WHERE portal.portal_owner = :usr ;";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(":usr", $_SESSION["userid"]);
                    }else{
                        $sql = "SELECT portal_id, portal.purl FROM `deployment` INNER JOIN portal on portal.pid = deployment.portal_id ;";
                        $stmt = $conn->prepare($sql);
                    }
                }
                else if($_GET['from'] == 'deploymentdetails'){
                    if($_SESSION['type'] != '100'){
                        $sql = "SELECT pid, portal.purl FROM portal WHERE pid NOT IN (SELECT deployment.portal_id from deployment) AND portal.portal_owner = :usr ";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(":usr", $_SESSION["userid"]);
                    }else{
                        $sql = "SELECT pid, portal.purl FROM portal WHERE pid NOT IN (SELECT deployment.portal_id from deployment)";
                        $stmt = $conn->prepare($sql);
                    }
                }
                else if($_GET['from'] == 'usr'){
                    $sql = "SELECT * FROM users where userid not in (10000)";
                    $stmt = $conn->prepare($sql);
                }
                else if($_GET['from'] == 'portl'){
                    $sql = "SELECT pid, purl FROM portal where pid in (select portal_id from deployment)";
                    $stmt = $conn->prepare($sql);
                }
            }elseif($_SERVER['REQUEST_METHOD'] === 'POST'){
                if($_POST['from'] == 'usr2'){
                $sql = "SELECT * FROM users where userid = :usr";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":usr", $_POST["usr"]);
                }
            }
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $options[] = $row;
            }
            echo json_encode($options);
        }catch (PDOException $e) {
            echo json_encode(array("response" => "Database error: " . $e->getMessage()));
        } catch (Exception $e) {
            echo json_encode(array("response" => "General error: " . $e->getMessage()));
        }finally{
            $conn = null;
        }
    }

    public function fetchscheduledetails(){
        /**
         * Return the deployment schedule details from database table
         */
        require("conn.php");
        try{ 
            $sql = "SELECT portalname,deployment_date,deployment_version,deployment_id FROM deployment INNER JOIN portal on deployment.portal_id = portal.pid where pid = :pid";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":pid", $_GET['id']);
            $stmt->execute();
            $details = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($details);
        }catch (PDOException $e) {
            echo json_encode(array("response" => "Database error: " . $e->getMessage()));
        } catch (Exception $e) {
            echo json_encode(array("response" => "General error: " . $e->getMessage()));
        }finally{
            $conn = null;
        }
    }

    public function changepassword(){
        /**
         * Change the password of currently logged in user 
         */
        session_start();
        require("conn.php");
        try{
            $pass = $_POST['newpassword'];
            $password = password_hash($pass, PASSWORD_DEFAULT);
            $sql = "UPDATE `users` SET `password`= :password WHERE userid = :userid ";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":userid", $_SESSION['userid']);
            $stmt->bindParam(':password',$password);
            $stmt->execute();
            self::useractionlog('change password');
            echo json_encode(array("response" =>"Password Changed successfully"));
        }catch (PDOException $e) {
            echo json_encode(array("response" => "Database error: " . $e->getMessage()));
        } catch (Exception $e) {
            echo json_encode(array("response" => "General error: " . $e->getMessage()));
        }
        finally{
            $conn = null;
        }
    }

    public function adduser(){
        /**
         * Add a new user to database storing all details and provide login functionality
         */
        require("conn.php");
        try {
            header('Content-Type: application/json');
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $name = $_POST['name'];
                $email = $_POST['email'];
                $phone = $_POST['phone'];
                $usertype = $_POST['usertype'];
                $pass = '';
                $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
                for ($i = 0; $i < 8; $i++) {
                    $n = rand(0, strlen($alphabet)-1);
                    $pass .= $alphabet[$n];
                }
                $password = password_hash($pass, PASSWORD_DEFAULT);
                $sql = $conn->prepare("INSERT INTO users(username, password, email, phone, type) VALUES (:name,:password,:email,:phone,:usertype)");
                $sql->bindParam(':name',$name);
                $sql->bindParam(':email',$email);
                $sql->bindParam(':phone',$phone);
                $sql->bindParam(':password',$password);
                $sql->bindParam(':usertype',$usertype);
                $sql->execute();
                require_once 'registermail.php';
                self::useractionlog('added user '.$name);
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
        /**
         * Handles the user login process
         * 
         * check the database and retrieve details of user with the email id entered
         * verifies the user entered password with the hashed password stored in the database
         * set session variables 
         */
        require("conn.php");
        header('Content-Type: application/json');
        try {
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
                    self::useractionlog('login');
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


    public function addportal(){
        /**
         * Adds a new portal to the database.
         */
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
                    self::useractionlog('add portal '.$portal_name);
                    echo json_encode(array("response" =>$portal_name." Portal Inserted Successfully"));
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
        /**
         * Add a new deployment for each portal 
         * 
         * save the details of the new deployment to database
         * handle file upload by checking if file is received and with error code 0
         * file is moved to a version specific folder for each portal
         * a new folder is created for each version if not present
         */
        require("conn.php");
        header('Content-Type: application/json');
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $portal_url = $_POST['portal_url'];
                $portal_version = $_POST['portal_version'];
                $deployment_date = $_POST['deployment_date'];
                $num_days = $_POST['num_days'];
                $deployment_note = $_POST['deployment_note'];
                if (isset($_FILES['deployment_plan']) && $_FILES['deployment_plan']['error'] == 0) {
                    $uploadDir = 'uploads/'.$portal_url.'/'.$portal_version.'/'; 
                    $uploadFile = $uploadDir . basename($_FILES['deployment_plan']['name']);
                    // create directory if not present 0777 indicates read write execute permission
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
                            self::useractionlog('added deployment '.$portal_url);
                            echo json_encode(array("response" =>"Deployment Details Added Successfully"));
                    } else {
                        echo json_encode(array("response"=>"File Error"));
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
        /**
         * Add the details of portals that require a change in deployment schedule
         * 
         * Requests submitted by user to the admin to change the deployment date is saved 
         */
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
                self::useractionlog('schedule change request');
                echo json_encode(array("response" =>"Schedule Change Added Successfully"));
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
        /**
         * Return data for datatable displaying the details of change requests
         */
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
        }catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }finally {
            $conn = null;
        }
    }

    public function adminwarning(){
        /**
         * Check for overlapping deployment dates and issue a warning.
         * 
         * checks whether the new deployment date range overlaps with any existing deployment dates of other deployments.
         * if overlap is present a warning stating there is a overlap in dates 
         * if no overlap is present returns a confirm message to admin
         */
        require('conn.php');
        try{
            if(isset($_POST['oldDate']) && isset($_POST['deployment_id']) ){
                $id = $_POST['deployment_id'];
                $olddate = $_POST['oldDate'];
                $newdate = $_POST['deployment_date'];
                $startDate = new DateTime($newdate);
                $endDate = (clone $startDate)->modify('+' . ($_POST['days'] - 1) . ' days');
                $selectdates = "SELECT deployment_id, deployment_date, required_days FROM `deployment` WHERE deployment_id <> :depid";
                $stmtseldates = $conn->prepare($selectdates);
                $stmtseldates->bindParam(":depid", $id, PDO::PARAM_INT);
                $stmtseldates->execute();
                $dates = $stmtseldates->fetchAll(PDO::FETCH_ASSOC);
                $overlaps = false;
                //check if the deployment dates o next deployment is having an overlap with the newly selected date
                foreach ($dates as $dateRow) {
                    $startDate2 = new DateTime($dateRow['deployment_date']);
                    $endDate2 = (clone $startDate2)->modify('+' . ($dateRow['required_days'] - 1) . ' days');
                    if ($startDate <= $endDate2 && $startDate2 <= $endDate) {
                        $overlaps = true;
                    }
                }
                if($overlaps){
                    echo json_encode(array("message"=>'Dates Overlap. Do You Really want to continue ?'));
                }else{
                    echo json_encode(array("message"=>'Do You Really want to continue ?'));
                }
            }
        }catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }finally {
            $conn = null;
        }
    }

    public function adminedit(){
        /**
         *  Handles the admin's edits of an existing deployment.
         * 
         * This function updates the deployment details and logs the changes in a changelog table.
         * It updates both the deployment and the associated portal information.
         * 
         * Calls file 'test.php' for updating the dates of other deployments adjusting to the change in deployment date of a portal
         */
        require('conn.php');
        require 'test.php';
        try{
            $id = $_POST['deployment_id'];
            $olddate = $_POST['oldDate'];
            $newdate = $_POST['deployment_date'];
            $sqlInsertChangelog2 = "INSERT INTO `changelog`(`deployment_id`, `old_date`, `new_date`, `change_date`, `change_time`, `info`) VALUES  (:deploymentId, :oldDate, :newDate,:changedate, :changetime, :changeDescription)";
            $stmtChangelog2 = $conn->prepare($sqlInsertChangelog2);
            $stmtChangelog2->bindParam(':deploymentId', $id);
            $stmtChangelog2->bindParam(':oldDate', $olddate);
            $stmtChangelog2->bindParam(':newDate', $newdate);
            $stmtChangelog2->bindValue(':changeDescription', 'adjusted by admin');
            $datenow = new DateTime();
            $formattedDate = $datenow->format('Y-m-d');
            $timenow = new DateTime();
            $formattedTime = $timenow->format('H:i:s');
            $stmtChangelog2->bindParam(':changedate', $formattedDate);
            $stmtChangelog2->bindParam(':changetime', $formattedTime);
            $stmtChangelog2->execute();
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
            self::useractionlog(fun: 'admin edit portal '.$_POST['portal_name']);
        }catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }finally {
            $conn = null;
        }
    }

    public function deploymentstable(){
        /**
         * Returns the data for datatable displaying the portal details
         */
        require('conn.php');
        session_start();
        try {
            if($_SESSION['type'] == 'admin'){
                $sql = "SELECT portal.purl, portal.portalname,deployment.deployment_date, users.username, deployment.required_days , deployment.deployment_id FROM `deployment` INNER JOIN portal ON deployment.portal_id = portal.pid INNER JOIN users on portal.portal_owner = users.userid order by deployment_date";
                $stmt = $conn->prepare($sql);

            }else{
                $sql = "SELECT portal.purl, portal.portalname,deployment.deployment_date, users.username, deployment.required_days , deployment.deployment_id FROM `deployment` INNER JOIN portal ON deployment.portal_id = portal.pid INNER JOIN users on portal.portal_owner = users.userid where portal.portal_owner = :usr order by deployment_date";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':usr',$_SESSION['userid']);
            }
            
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
        }finally {
            $conn = null;
        }
    }


    public function viewdetails(){
        /**
         * Returns the details of diffrent portal deployments.
         */
        require("conn.php");
        try{
            if($_GET['from'] == 'schedule'){
                $sql = "SELECT deployment_version,deployment_date, deployment_note, required_days,portalname, purl, version, pfeatures, new_date, user_note, username, deployment_plan, schhedulechange.deployment_id FROM `deployment` INNER JOIN portal ON deployment.portal_id = portal.pid INNER JOIN schhedulechange on schhedulechange.deployment_id = deployment.deployment_id INNER JOIN users on schhedulechange.user_id = users.userid where purl = :purl";
            }elseif($_GET["from"] == "deployment"){
                $sql = "SELECT deployment_id,deployment_version,deployment_date, deployment_note, required_days,portalname, purl, version, pfeatures, username, deployment_plan, portal_id FROM `deployment` INNER JOIN portal ON deployment.portal_id = portal.pid INNER JOIN users on portal.portal_owner = users.userid where purl = :purl ";
            }
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":purl", $_GET['purl']);
            self::useractionlog('view details '.$_GET['purl']);
            $stmt->execute();
            $details = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($details);
        }catch (PDOException $e) {
            echo json_encode(array("response" => "Database error: " . $e->getMessage()));
        } catch (Exception $e) {
            echo json_encode(array("response" => "General error: " . $e->getMessage()));
        }finally{
            $conn = null;
        }
    }

    public function managechange(){
        /**
         * Set the status of each change request after admin accepts or rejects the change
         */
        require("conn.php");
        header('Content-Type: application/json');
        try{
            $sql = "UPDATE schhedulechange SET change_status= :status WHERE schhedulechange.deployment_id  = :deployment_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":status", $_POST['status']);
            $stmt->bindParam(":deployment_id", $_POST['deployment_id']);
            $stmt->execute();
            self::useractionlog('admin accept deployment '.$_POST['deployment_id']);
            echo json_encode(array("response" => "Success"));
        }catch (PDOException $e) {
            echo json_encode(array("response" => "Database error: " . $e->getMessage()));
        } catch (Exception $e) {
            echo json_encode(array("response" => "General error: " . $e->getMessage()));
        }finally{
            $conn = null;
        }
    }

    public function disabledate(){
        /**
         * Return the dates of deployments scheduled as an array
         * 
         * Used to disable dates in datepicker 
         */
        require("conn.php");
        try{
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
                $x = 0;
                foreach($datearr as $startDate){
                    $startDate = new DateTime($startDate);
                    if(date('l',strtotime($startDate->format('Y-m-d'))) == 'Saturday'){
                        $x += 2;
                    }
                }
                $y = new DateTime($datearr[sizeof($datearr) - 1]);
                for( $i = 0 ; $i < $x  ; $i++ ){
                    $y->modify("+1 days");
                    array_push($datearr, $y->format('Y-m-d'));
                }
                $allDates = array_merge($allDates, $datearr);  
            }
            echo json_encode($allDates);  
        }catch (PDOException $e) {
            echo json_encode(array("response" => "Database error: " . $e->getMessage()));
        } catch (Exception $e) {
            echo json_encode(array("response" => "General error: " . $e->getMessage()));
        }finally{
            $conn = null;
        }

    }

    public function message(){
        /**
         * Used to show the changes in schedule
         * 
         * returns the change info of changes in deployment table
         * displayed to respective users of changes in their portal deployments
         */
        require("conn.php");
        session_start();
        try{
            $stmt = $conn->prepare("select changelog.log_id, changelog.view, changelog.old_date,changelog.new_date,changelog.change_date,changelog.change_time,changelog.info, portal.portalname,portal.purl,users.username FROM `changelog` INNER JOIN deployment on changelog.deployment_id = deployment.deployment_id INNER JOIN portal on portal.pid =deployment.portal_id INNER join  users on portal.portal_owner = users.userid where users.userid = :id");
            $stmt->bindParam(":id", $_SESSION['userid']);
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($res);
        }catch (PDOException $e) {
            echo json_encode(array("response" => "Database error: " . $e->getMessage()));
        } catch (Exception $e) {
            echo json_encode(array("response" => "General error: " . $e->getMessage()));
        }finally{
            $conn = null;
        }
    }

    public function readmessage(){
        /**
         * Update column in the database if the user has seen the message 
         * 
         * used to notify the user only when a new notification is available
         */
        require("conn.php");
        try{
            $stmt = $conn->prepare("update changelog set view = 1 where log_id = :id");
            $stmt->bindParam(":id", $_GET['id']);
            $stmt->execute();
            echo json_encode("success");
        }catch (PDOException $e) {
            echo json_encode(array("response" => "Database error: " . $e->getMessage()));
        } catch (Exception $e) {
            echo json_encode(array("response" => "General error: " . $e->getMessage()));
        }finally{
            $conn = null;
        }
    }

    public function edituser(){
        /**
         * Edit the user details by the admin
         */
        require("conn.php");
        session_start();
        try{
            $stmt = $conn->prepare("UPDATE `users` SET `username`=:name,`email`=:email,`phone`=:phone,`type`=:type WHERE userid = :id");
            $stmt->bindParam(":name", $_POST['name']);
            $stmt->bindParam(":email", $_POST['email']);
            $stmt->bindParam(":phone", $_POST['phone']);
            $stmt->bindParam(":type", $_POST['type']);
            $stmt->bindParam(":id", $_POST['id']);
            $stmt->execute();
            self::useractionlog('edit user '.$_POST['name']);
            echo json_encode(array("message" => "Data Updated"));
        }catch (PDOException $e) {
            echo json_encode(array("response" => "Database error: " . $e->getMessage()));
        } catch (Exception $e) {
            echo json_encode(array("response" => "General error: " . $e->getMessage()));
        }finally{
            $conn = null;
        }
    }

    public function emailcheck(){
        /**
         * Check whether a particular email is present in the database
         */
        require('conn.php');
        try {
            $sql = "SELECT email from users where email = :email";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":email", $_GET["email"]);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($data)) {
                header('Content-Type: application/json');
                echo json_encode("ok");
            } else {
                echo json_encode("xx");
            }
        } catch(PDOException $e) {
            echo json_encode(array("message" =>"Connection failed: " . $e->getMessage()));
        }finally {
            $conn = null;
        }
    }

    public function pcheck(){
        /**
         * Check whether a particular email is present in the database
         */
        require('conn.php');
        try {
            $sql = "SELECT portalname,purl from portal where portalname = :portalname";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":portalname", $_POST["portal_name"]);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $flag = 1;
            if (!empty($data)) {
                header('Content-Type: application/json');
                echo json_encode("ok");
                $flag = 0;
            }else{
                $sql1 = "SELECT portalname,purl from portal where purl = :purl";
                $stmt1 = $conn->prepare($sql1);
                $stmt1->bindParam(":purl", $_POST["portal_url"]);
                $stmt1->execute();
                $data1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($data1)) {
                    header('Content-Type: application/json');
                    echo json_encode("ok2");
                    $flag = 0;
                }
            } 
            if($flag == 1) {
                echo json_encode("xx");
            }
        } catch(PDOException $e) {
            echo json_encode(array("message" =>"Connection failed: " . $e->getMessage()));
        }finally {
            $conn = null;
        }
    }


    public function useractionlog($fun){
        require("conn.php");
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $stmt = $conn->prepare("INSERT INTO useractionlog(userid, action, datetime) VALUES (:user,:action,CURRENT_TIMESTAMP)");
        $stmt->bindParam(":user", $_SESSION['userid']);
        $stmt->bindParam(':action', $fun);
        $stmt->execute();
    }

    public function userlog(){
        /**
         * Returns the data for datatable displaying the userlogs
         */
        require('conn.php');
        try {
            $sql = "SELECT users.username, useractionlog.action, useractionlog.datetime from users INNER JOIN useractionlog on users.userid = useractionlog.userid;";
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
        }finally {
            $conn = null;
        }
    }

}

try{
    /**
     * Receive the name of function from user and call the function
     * 
     * Create an object and call the function passed from the page
     */
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