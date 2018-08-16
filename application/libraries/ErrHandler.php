<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class ErrHandler {

    public static $err = array(
        "ERROR_PARAM" => [-1, "提交錯誤"],
        "ERROR_USERNAME" => [-2, "帳號錯誤"],
        "ERROR_PASSWORD" => [-3, "密碼錯誤"],
        "ERROR_USER_PASSWORD" => [-5, "帳號或密碼錯誤"],
        
        
        
        "ERROR_SYSTEM" => [-100000, "系統錯誤"]
    );

}
