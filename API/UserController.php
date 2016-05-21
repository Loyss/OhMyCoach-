<?php
require("config.php");

header('Access-Control-Allow-Origin: *');

$action = new UserController();


class UserController
{

    var $params = array();
    var $url = '';

    public function __construct()
    {
        $this->getParams();
        $this->initialize();
    }

    private function getParams()
    {
        $this->params = file_get_contents("php://input");
        $this->params = json_decode($this->params);
    }

    private function initialize()
    {
        if($this->params->type == "user"){

            if ($this->params->action == "findAll"){
                $this->findAllUser();
            }
            if ($this->params->action == "register"){
                $this->register();
            }
            if ($this->params->action == "login"){
                $this->login();
            }
        }

    }

    /*************************** CRUD **********************************/


    private function findAllUser()
    {

        $pdo = Database::connect();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "Select * from omc_users";
        $q = $pdo->prepare($sql);
        $q->execute();
        $data = $q->fetchAll(PDO::FETCH_ASSOC);

        Database::disconnect();
        echo json_encode($data);

    }

    private function register(){
        $user_pseudo = $this->params->user->user_pseudo;
        $user_email = $this->params->user->user_email;
        $user_password = $this->params->user->user_password;
        $coach_name = $this->params->user->coach_name;
        $data['success'] = "";

        if ( (!empty($user_pseudo) && !empty($user_email) && !empty($user_password) && !empty($coach_name) ) ) {

            $pdo = Database::connect();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "SELECT user_pseudo, user_email FROM omc_users WHERE user_pseudo, user_email = ?";
            $q = $pdo->prepare($sql);
            $q->execute(array($user_pseudo, $user_email));
            $response= $q->fetch();
            if($response == false) {
                $sql = "INSERT INTO users (user_pseudo, user_email, user_password, coach_name) values(?, ?, ?, ?)";
                $q = $pdo->prepare($sql);
                $q->execute(array($user_pseudo, $user_email, md5($user_password), $coach_name));
                $result = $pdo->lastInsertId();
                if($result)
                    $data["success"] = true;
                else
                    $data["success"] = false;
            }
            Database::disconnect();
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Content-type: application/json');
            echo json_encode($data);
        }
    }

    private function login()
    {
        if (!empty($this->params->user)) {

            $user_email = $this->params->user->user_email;
            $user_password = $this->params->user->user_password;

            if ( (!empty($user_email) && !empty($user_password)) )
            {
                $pdo = Database::connect();
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $sql = "SELECT user_email, user_id FROM omc_users WHERE user_email = ? AND user_password = ?";
                $q = $pdo->prepare($sql);
                $q->execute(array($user_email, md5($user_password)));
                $response = $q->fetch(PDO::FETCH_ASSOC);
                $data['user'] = $response;

                if ($response == false) {
                    $data["success"] = false;
                }
                else {
                    $data["success"] = true;
                }
                Database::disconnect();
                Database::disconnect();
                //RESPONSE
                header('Cache-Control: no-cache, must-revalidate');
                header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
                header('Content-type: application/json');
                echo json_encode($data);
            }
        }
    }


}