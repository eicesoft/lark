<?php
namespace Lark\Service;


use Lark\Core\Console;
use Lark\Core\Kernel;

/**
 * Class BaseService
 * @package Lark\Service
 * @author kelezyb
 */
abstract class BaseService
{
    /**
     * @var \Swoole\Server
     */
    protected $server;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var array
     */
    protected $params;

    protected $name;

    /**
     * BaseService constructor.
     * @param string $host
     * @param int $port
     * @param array $params
     */
    public function __construct($host='0.0.0.0', $port=10086, $params=[])
    {
        $this->host = $host;
        $this->port = $port;
        $this->params = $params;
    }

    /**
     * Registry service events
     */
    public function registryBaseEvents()
    {
        $this->server->on('Start', [$this, 'onStart']);
        $this->server->on('ManagerStart', [$this, 'onManagerStart']);
        $this->server->on('WorkerStart', [$this, 'onWorkerStart']);
        $this->server->on('WorkerStop', [$this, 'onWorkerStop']);
        $this->server->on('WorkerError', [$this, 'onWorkerError']);
        $this->server->on('Shutdown', [$this, 'onShutdown']);
        $this->registryEvents();
    }

    abstract function registryEvents();

    /**
     * service start event
     * @param \swoole_server $serv
     */
    public function onStart($serv) {
        swoole_set_process_name("Lark master #" . $this->port);
    }

    /**
     * manager process start
     * @param \swoole_server $serv
     */
    public function onManagerStart($serv) {
        swoole_set_process_name("Lark manager #". $this->port);

//        $this->init_tasks();
    }

    private function init_tasks()
    {
        $app = Kernel::Instance()->get('app');
        $tasks = $app->get('tasks');
        foreach($tasks as $task) {
            \Swoole\Timer::tick($task['interval'], function($timer_id, ...$params) use($task) {
                $task_obj = Kernel::Instance()->newInstance($task['class'], $params);
                $task_obj->execute();
            }, $task['params']);
        }
    }

    /**
     * worker process start event
     * @param \swoole_server $serv
     * @param int $worker_id
     */
    public function onWorkerStart($serv, $worker_id) {
        $pid = posix_getpid();

        if ($worker_id >= $serv->setting['worker_num']) {
            swoole_set_process_name("Lark task_{$worker_id} #{$this->port}");
            Console::info("Lark %s service task worker %s start.", [$this->name, $pid]);
        } else {
            swoole_set_process_name("Lark worker_{$worker_id} #{$this->port}");
            Console::info("Lark %s service worker %s start.", [$this->name, $pid]);
        }
    }

    /**
     * worker process stop event
     * @param \swoole_server $serv
     * @param int $worker_id
     */
    public function onWorkerStop($serv, $worker_id) {
        Console::info("Lark %s service worker %s stop.", [$this->name, $worker_id]);
    }

    /**
     * worker process error event
     * @param \swoole_server $serv
     * @param int $worker_id
     * @param int $worker_pid
     * @param int $exit_code
     * @param int $signal
     */
    public function onWorkerError($serv, $worker_id, $worker_pid, $exit_code, $signal) {
        Console::warn("Worker_%s - (%s, %s)", [$worker_pid, $exit_code, $signal]);
    }

    /**
     * service shutdown event
     * @param $serv
     */
    public function onShutdown($serv) {
        Console::info("Lark %s service will shutdown...", [$this->name]);
    }
}