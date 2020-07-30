<?php
namespace Lark\Service;


use Lark\Core\Console;
use Lark\Core\Kernel;
use Lark\Loader\RpcLoader;
use Lark\Rpc\Rpc;
use Lark\Rpc\RpcException;
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * Rpc tcp Service
 * @package Lark\Service
 */
class RpcService extends BaseService
{
    /**
     * @var string
     */
    private $requestId;

    /**
     * @var array
     */
    private $rpcRegistrys;

    /**
     * RpcService constructor.
     */
    public function __construct($host = '0.0.0.0', $port = 10096, $params = [])
    {
        parent::__construct($host, $port, $params);
        $this->name = "Rpc";
        $this->server = new \Swoole\Http\Server($this->host, $this->port);
        $this->server->set($this->params);
        $this->requestId = 1;
        $load = RpcLoader::Instance();
        $this->rpcRegistrys = $load->getLoadMetas();
    }

    /**
     * Registry events
     */
    function registryEvents()
    {
        $this->server->on('Request', [$this, 'onRequest']);
    }

    public function run()
    {
        Console::info("Lark %s service run in %s:%s", [$this->name, $this->host, $this->port]);

        $this->registryBaseEvents();
        $this->server->start();
    }

    private function setRequestId($requestId)
    {
        $this->requestId = $requestId;
    }

    private function getRequestId($payload)
    {
        if (isset($payload['id'])) {
            $this->setRequestId($payload['id']);
        }

        return $this->requestId;
    }

    /**
     * @param $payload
     * @return array
     * @throws \Lark\Core\Exception
     */
    private function rpcHandle($payload)
    {
        try {
            if (!isset($payload['jsonrpc']) ||
                !isset($payload['method']) ||
                !is_string($payload['method']) ||
                $payload['jsonrpc'] !== '2.0' ||
                (isset($payload['params']) && !is_array($payload['params']))) {
                Console::warn( 'Rpc Invalid Request');
                return [
                    "jsonrpc" => "2.0",
                    "error" => [
                        "code" => -32600,
                        "message" => 'Invalid Request'
                    ],
                    "id" => $this->requestId
                ];
            }

            if (!isset($this->rpcRegistrys[$payload['method']])) {
                Console::warn( 'Rpc service method [%s] not found', [$payload['method']]);
                return [
                    "jsonrpc" => "2.0",
                    "error" => [
                        "code" => -32601,
                        "message" => "Method [{$payload['method']}] not found"
                    ],
                    "id" => $this->requestId
                ];
            }

            /** @var Rpc $rpcRegistry */
            $rpcRegistry = $this->rpcRegistrys[$payload['method']];

            Console::info( 'Rpc service method [%s] => %s be called', [$payload['method'], $rpcRegistry['class']]);
            Kernel::Instance()->call($rpcRegistry['object'], 'setParams', [$payload['params']]);
            $result = Kernel::Instance()->call($rpcRegistry['object'], 'execute', );

            return ["jsonrpc" => "2.0", "result" => $result, "id" => $this->getRequestId($payload)];
        } catch (\Exception $ex) {
            return ["jsonrpc" => "2.0",
                "error" => [
                    "code" => $ex->getCode(),
                    "message" => $ex->getMessage()
                ],
                "id" => $this->requestId
            ];
        }
    }

    /**
     * request handler
     * @param Request $request
     * @param Response $response
     */
    public function onRequest($request, $response)
    {
        try {
            $raw = $request->rawContent();
            $payloads = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

            if (array_keys($payloads) === range(0, count($payloads) - 1)) { //mulit request
                $resp = [];
                foreach($payloads as $payload) {
                    $resp[] = $this->rpcHandle($payload);
                }
            } else {
                $resp = $this->rpcHandle($payloads);
            }
            $response->end(json_encode($resp));
        } catch (\Exception $ex) {
            $response->end(json_encode([
                "jsonrpc" => "2.0",
                "error" => [
                    "code" => $ex->getCode(),
                    "message" => $ex->getMessage()
                ],
                "id" => $this->requestId
            ]));
        }
    }
}