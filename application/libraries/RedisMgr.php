<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class RedisMgr {

    private static $redis = null;

    function __construct() {
        
    }

    public static function instance() {
        if (!self::$redis || !self::$redis->ping() != "+PONG") {
            $redis = new Redis();
            $redis->connect(REDIS_HOST, REDIS_PORT);
            $redis->select(REDIS_SELECT);
            self::$redis = $redis;
        }
        return self::$redis;
    }

}
