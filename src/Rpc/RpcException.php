<?php


namespace Lark\Rpc;


use Lark\Core\Exception;

class RpcException extends Exception
{
    /**
     * @Message("RPC service exception: %s")
     */
    const RPC_EXCEPTION_CODE = 51001;
}