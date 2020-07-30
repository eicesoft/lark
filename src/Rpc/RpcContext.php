<?php
namespace Lark\Rpc;

use Lark\Core\Context;

/**
 * Class Rpc
 * @package Lark\Rpc
 */
abstract class RpcContext extends Context
{
    protected $params;

    /**
     * @param $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return |null
     */
    public function getParam($key, $default=null)
    {
        return $this->params[$key] ?? $default;
    }

    public abstract function execute();
}