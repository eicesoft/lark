<?php

namespace Lark\Routing;


use Lark\Core\Kernel;

/**
 * Http Response
 * @package Lark\Routing
 */
class Response
{
    /**
     * @var \Swoole\Http\Response
     */
    private $response;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var array
     */
    private $cookies;

    /**
     * @var array
     */
    private $contents;

    private $status = 200;

    private $isFlush = false;

    /**
     * Response constructor.
     * @param \Swoole\Http\Response $response
     */
    public function __construct($response)
    {
        $this->response = $response;
        $this->headers = [];
        $this->cookies = [];
        $this->contents = [];
    }

    public function end($html)
    {
        $this->contents[] = $html;
//        $this->response->end($html);
        $this->flush();
    }

    public function header($key, $value, $ucwords = true)
    {
//        $this->response->header($key, $value, $ucwords);
        $this->headers[] = [$key, $value, $ucwords];
        return $this;
    }

    public function cookie($key, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false, $samesite = '')
    {
//        $this->response->cookie($key, $value, $expire, $path, $domain, $secure, $httponly, $samesite);
        $this->cookies[] = [$key, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false, $samesite = ''];
        return $this;
    }

    public function write($data)
    {
        $this->contents[] = $data;
//        $this->response->end($data);
        return $this;
    }

    public function status($http_status_code)
    {
//        $this->response->status($http_status_code);
        $this->status = $http_status_code;
        return $this;
    }

    public function flush()
    {
        if (!$this->isFlush) {
            $this->isFlush = true;
            $this->response->status($this->status);
            foreach ($this->headers as $header) {
                Kernel::Instance()->call($this->response, 'header', $header);
            }

            foreach ($this->cookies as $cookie) {
                Kernel::Instance()->call($this->response, 'cookie', $cookie);
            }

            $contents = join('', $this->contents);
            if (!empty($contents)) {
                $this->response->end($contents);
            }
        }
    }

    public function redirect($uri)
    {
        $this->isFlush = true;
        $this->response->redirect($uri);
    }

    public function sendfile($filename)
    {
        $this->isFlush = true;
        $this->response->sendfile($filename);
        return $this;
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->response, $name], $arguments);
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}