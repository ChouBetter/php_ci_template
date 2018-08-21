<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class User extends MY_Controller {

    public function __construct() {
        $this->ignoreVerifyMethods = array('login');
        parent::__construct();
    }

    public function login() {
        $this->CheckParam(array('username', 'password'));

        $username = $this->DATA['username'];
        if (!preg_match('/^\w{4,16}$/', $username))
            $this->echoError("ERROR_USERNAME");

        $password = $this->DATA['password'];
        if (!preg_match('/^\w{4,16}$/', $password))
            $this->echoError("ERROR_PASSWORD");

        if ($username != $password)
            $this->echoError("ERROR_USER_PASSWORD");

        $content = array('uid' => 1, 'username' => $username);

        $jwt = jwt_helper::create($content);
        header('Authorization: ' . $jwt);

        $this->echoSuccess(array("user" => $content));
    }

    public function check() {
        $this->echoSuccess(array("serverTime" => date('Y-m-d H:i:s')));
    }

}
