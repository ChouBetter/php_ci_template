<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    protected $DATA;
    protected $ignoreVerifyMethods = array();
    protected $uid;
    protected $sessionid;

    public function __construct() {
        parent::__construct();
        header('Content-Type: application/json; charset=utf-8');

        if ($this->input->method(true) == "POST") {
            $content = file_get_contents('php://input');
            $this->DATA = json_decode($content, true);
            if (!$content || !($this->DATA = json_decode($content, true)))
                $this->echoErrorParam();
        }

        $method = $this->router->fetch_method();
        if (in_array($method, $this->ignoreVerifyMethods))
            return;

        $header = $this->input->get_request_header('Authorization', TRUE);
        list($token) = sscanf($header, 'Token %s');
        if ($header != '' && jwt_helper::validate($token)) {
            $obj = jwt_helper::decode($token)->userId;
            $this->uid = $obj->uid;
            $this->username = $obj->username;
        } else
            show_error("Permission denied", 401, "Please check your token.");
    }

    function CheckParam($params) {
        foreach ($params as $p)
            if (!isset($this->DATA[$p]))
                $this->echoErrorParam();
    }

    function echoErrorParam() {
        $this->echoError("ERROR_PARAM");
    }

    function echoError($code, $param = array()) {
        $this->load->library('ErrHandler');
        $err = ErrHandler::$err[$code];
        if (!$err)
            $err = ErrHandler::$err["ERROR_SYSTEM"];
        echo json_encode(array_merge(array('result' => $err[0], 'code' => $code, 'content' => $err[1]), $param));
        exit;
    }

    function echoSuccess($response = array()) {
        echo json_encode(array_merge(array('result' => 0), $response));
        exit;
    }

    private function verifySession($sessionId, $accessAction) {
        $this->load->library('RedisMgr');
        $redis = RedisMgr::instance();
        if (!$redis->hexists($sessionId, 'expiredTime')) {
            return 0;
        }
        $getRes = $redis->hgetall($sessionId);
        if ($getRes['expiredTime'] > time() && $getRes['enable'] == 1) {
            $redis->setTimeout($sessionId, SESSION_LIFE_TIME);
            $redis->hmset($sessionId, array(
                "enable" => "1",
                "accessTime" => time(),
                "expiredTime" => time() + SESSION_LIFE_TIME,
                "accessAction" => $accessAction,
            ));
            return $getRes['uid'];
        } else {
            return 0;
        }
    }

}
