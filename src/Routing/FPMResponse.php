<?php


namespace Lark\Routing;

/**
 * FPM Response hook
 * @package Lark\Routing
 */
class FPMResponse
{
    public function status($http_status_code)
    {
        header('Status:' . $http_status_code);
    }

    public function header($key, $value, $ucwords = true)
    {
        $key = $ucwords ? ucwords($key) : $key;
        header("$key: $value");
    }

    public function cookie($key, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false, $samesite = '')
    {
        setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }

    public function end($str)
    {
        echo $str;
    }
}