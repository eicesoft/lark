<?php

namespace Lark\Service;

use Lark\Core\Console;
use Lark\Core\Kernel;
use Lark\Event\EventManager;
use Lark\Event\Local\ExceptionEvent;
use Lark\Event\Local\RequestEvent;
use Lark\Routing\Dispacther;
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * Class Service
 * @package Lark\Service
 * @author
 */
final class HttpService extends BaseService
{
    /**
     * Service constructor.
     * @param string $host
     * @param int $port
     * @param array $params
     */
    public function __construct($host = '0.0.0.0', $port = 10086, $params = [])
    {
        parent::__construct($host, $port, $params);
        $this->name = "Micro";
        $this->server = new \Swoole\Http\Server($this->host, $this->port);
        $this->server->set($this->params);
    }

    /**
     * Registry events
     */
    function registryEvents()
    {
        $this->server->on('Request', [$this, 'onRequest']);
        $this->server->on('Task', [$this, 'onTask']);
        $this->server->on('Finish', [$this, 'onFinish']);
    }

    public function run()
    {
        Console::info("Lark %s service run in %s:%s", [$this->name, $this->host, $this->port]);

        $this->registryBaseEvents();
        $this->server->start();
    }

    /**
     * task process event
     * @param \swoole_server $serv
     * @param int $task_id
     * @param int $from_id
     * @param mixed $data
     */
    public function onTask($serv, $task_id, $from_id, $data)
    {

    }

    public function onFinish($serv, $task_id, $data)
    {

    }

    /**
     * request handler
     * @param Request $request
     * @param Response $response
     */
    public function onRequest($request, $response)
    {
        register_shutdown_function(function () use ($response) {
            //Fatal error handler
            $error = error_get_last();
//            Console::error($error['message']);
            EventManager::Instance()->trigger(new ExceptionEvent(new \Exception($error['message'], 50000)));
            switch ($error['type'] ?? null) {
                case E_ERROR :
                case E_PARSE :
                case E_CORE_ERROR :
                case E_COMPILE_ERROR :
                    $response->status(500);
                    $data = [
                        'code' => '500',
                        'core_code' => $error['type'],
                        'message' => 'Core Error:',
                        'data' => $error['message']
                    ];
                    $response->header('Content-Type', 'application/json');
                    $response->end(json_encode($data));
                    break;
            }
        });
        $start = microtime(true);
        try {
            EventManager::Instance()->trigger(new RequestEvent($request));
            $kernel = Kernel::Instance();
            $dispacher = $kernel->newInstance(Dispacther::class, [$request, $response]);
            $kernel->call($dispacher, 'execute');
            unset($dispacher);
        } catch (\Exception $ex) {
            EventManager::Instance()->trigger(new ExceptionEvent($ex));
            display_exception($ex);
            Console::error($ex->getMessage() );
            $kernel = Kernel::Instance();
            $dispacher = $kernel->newInstance(Dispacther::class, [$request, $response]);
            $dispacher->exception($ex);
        }
        $end = microtime(true);
        Console::info("Http request %s run time is %0.4f ms", [$request->server['path_info'], ($end - $start) * 1000]);
    }
}