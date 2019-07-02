<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    protected $DATA;
    protected $ignoreVerifyMethods = array();
    protected $uid;
    protected $sessionid;

    public function __construct() {
        parent::__construct();
        header('Access-Control-Allow-Origin: *');
        if ($this->input->method(true) == "POST")
            $this->DATA = json_decode(file_get_contents('php://input'), true);

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

    function echoError($code = "ERROR_SYSTEM", $param = array(), $replace = null) {
        header('Content-Type: application/json; charset=utf-8');
        $this->load->library('ErrHandler');
        if (!array_key_exists($code, ErrHandler::$err))
            $code = "ERROR_UNKNOWN";
        $err = ErrHandler::$err[$code];
        echo json_encode(array_merge(array('result' => $err[0], 'code' => $code, 'content' => $replace ? str_replace('%s', $replace, $err[1]) : $err[1]), $param), JSON_NUMERIC_CHECK);
        exit;
    }

    function echoSuccess($response = array()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge(array('result' => 0), $response), JSON_NUMERIC_CHECK);
        exit;
    }

    private function verifySession($accessAction) {
        $this->load->library('RedisMgr');
        $redis = RedisMgr::instance();
        if (!$redis->hexists($this->sessionKey, 'expiredTime'))
            return 0;

        $getRes = $redis->hgetall($this->sessionKey);
        if ($getRes['expiredTime'] > time() && $getRes['enable'] == 1) {
            $redis->setTimeout($this->sessionKey, SESSION_EXPIRE_TIME);
            $redis->hmset($this->sessionKey, array(
                "enable" => "1",
                "accessTime" => time(),
                "expiredTime" => time() + SESSION_EXPIRE_TIME,
                "accessAction" => $accessAction,
            ));
            return $getRes['uid'];
        } else
            return 0;
    }

    protected function _curl($url, $data = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($data) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 1000);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 4000);
        $res = curl_exec($ch);
        $info = curl_getinfo($ch);
        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_errno > 0)
            $this->echoError("ERROR_INTERNAL", array("errno" => $curl_errno, "err" => $curl_error));
        elseif ($info['http_code'] != '200')
            $this->echoError("ERROR_INTERNAL", array("http_code" => $info['http_code'], "r" => $res));

        return json_decode($res, TRUE);
    }

}
