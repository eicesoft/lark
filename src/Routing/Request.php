<?php
/**
 * Created by PhpStorm.
 * User: eices
 * Date: 2019-12-04
 * Time: 11:38
 */

namespace Lark\Routing;


/**
 * Request
 * @package Lark\Routing
 */
final class Request
{
    /**
     * @var \Swoole\Http\Request
     */
    private $request;

    private $post;

    /**
     * Request constructor.
     * @param \Swoole\Http\Request $request
     */
    public function __construct($request)
    {
        $this->request = $request;
        $this->parse_data();
    }

    /**
     * 解析XML
     * @param \SimpleXMLElement $xml
     * @return array
     */
    private function parse_xml($xml)
    {
        $array = [];
        foreach($xml as $k => $v) {
            if ($v->count() == 0) {
                $res = (string) $v;
            } else {
                $res = $this->parse_xml($v);
            }
            $array[$k] = $res;
        }
        return $array;
    }

    /**
     * 解析请求
     */
    private function parse_data()
    {
        if (strtoupper($this->server('request_method')) == 'POST') {
            if ($this->request->post == null) {
                $content_type = $this->header('content-type');
                switch ($content_type) {
                    case 'text/plain':
                        $content = $this->request->rawContent();
                        $posts = explode('&', $content);
                        $this->post = [];
                        foreach ($posts as $post) {
                            list($k, $v) = explode('=', urldecode($post));
                            $this->post[$k] = $v;
                        }
                        break;
                    case 'application/xml':
                        $content = $this->request->rawContent();
                        $xml = simplexml_load_string($content);
                        $this->post = $this->parse_xml($xml);
                        break;
                    default:
                        try {
                            $this->post = $this->request->rawContent();
                        } catch (\Exception $ex) {
                            $this->post = [];
                        }
                        break;
                }
            } else {
                $this->post = $this->request->post;
            }
        } else {
            $this->post = [];
        }
    }

    public function get($key, $default = null)
    {
        return isset($this->request->get[$key]) ? $this->request->get[$key] : $default;
    }

    public function post($key, $default = null)
    {
        return isset($this->post[$key]) ? $this->post[$key] : $default;
    }

    public function header($key, $default = null)
    {
        return isset($this->request->header[$key]) ? $this->request->header[$key] : $default;
    }

    public function cookie($key, $default = null)
    {
        return isset($this->request->cookie[$key]) ? $this->request->cookie[$key] : $default;
    }

    public function file($key, $default = null)
    {
        return isset($this->request->files[$key]) ? $this->request->files[$key] : $default;
    }

    public function server($key, $default = null)
    {
        return isset($this->request->server[$key]) ? $this->request->server[$key] : $default;
    }

    /**
     * @return \Swoole\Http\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return mixed
     */
    public function getPosts()
    {
        return $this->post;
    }

    /**
     * @return mixed
     */
    public function getGets()
    {
        return $this->request->get ?? [];
    }

    /**
     * @return mixed
     */
    public function getHeader()
    {
        return $this->request->header;
    }

    /**
     * @return mixed
     */
    public function getRaw($format=false) {
        $contentType = $this->header('content-type');
        $raw = $this->request->rawContent();
        try {
            if ($format) {
                $data = json_decode($raw, true);
            } else {
                $data = $raw;
            }
        } catch (\JsonException $ex) {
            $data = $raw;
        }
        return $data;
    }

    /**
     * @param string $key
     * @param mixed $val
     * @param mixed $default
     * @return mixed|null
     */
    public function session($key, $val=null, $default=null)
    {
        $session_id = $this->header(Session::SESSION_ID,
            md5(strval(microtime(true))));

        $session = new Session($session_id);

        if ($val == null) {
            return $session[$key] ?? $default;
        } else {
            $session[$key] = $val;
        }
    }
}