<?php

namespace Lark\Routing;

use Lark\Cache\CacheFactory;
use Lark\Core\Config;
use Lark\Core\Context;
use Lark\Core\Exception;
use Lark\Core\Kernel;
use Lark\Core\Lark;
use Lark\Database\Entity;
use Lark\Middleware\Middleware;
use Lark\Middleware\RouterMiddleware;
use Monolog\Logger;

/**
 * Class Dispacther
 * @package Lark\Routing
 * @author kelezyb
 */
class Dispacther extends Context
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var array
     */
    private $routes;

    /**
     * Dispacther constructor.
     * @param $request
     * @param $response
     */
    public function __construct($request, $response)
    {
        $this->request = new Request($request);
        $this->response = new Response($response);

        Kernel::Instance()->registry('request', $this->request);
        Kernel::Instance()->registry('response', $this->response);
        $this->response->header("Server", "Lark micro service " . Lark::VERSION);

        $loader = \Lark\Loader\ControllerLoader::Instance();
        $this->routes = $loader->getLoadMetas();
        $this->initMiddleware();
    }


    /**
     * 初始化中间件
     * @throws \ReflectionException
     */
    public function initMiddleware(): void
    {
        $this->middlewares = [];
        $middware_configs = Config::get(null, 'middlewares');
        foreach ($middware_configs as $middware_config) {
            $obj = Kernel::Instance()->newInstance($middware_config, []);
            if ($obj instanceof Middleware) {
                $this->middlewares[] = $obj;
            }
        }
    }

    /**
     * 中间件处理
     */
    public function handleMidlewares()
    {
        foreach ($this->middlewares as $middleware) {
            $middleware->handle($this->request, $this->response);
        }
    }
    
    /**
     * @throws \ReflectionException
     */
    public function execute()
    {
        try {
            $this->handleMidlewares();

            $kernel = Kernel::Instance();
            $router = $kernel->newInstance(Router::class, []);
            $kernel->call($router, 'execute', [$this->request, $this->response]);
        } catch (\Exception $ex) {
            logger($ex->getMessage(), [], Logger::ERROR);
            $this->exception($ex);
        }
    }

    /**
     * @param Exception $ex
     */
    public function exception($ex)
    {
        $data = [
            'code' => $ex->getCode(),
            'message' => $ex->getMessage(),
        ];
        $this->response->status(200)->header('Content-Type', 'application/json')->end(json_encode($data));
    }

    /**
     *
     */
    public function __destruct()
    {
        Kernel::Instance()->unregistry('request');
        Kernel::Instance()->unregistry('response');
        Kernel::Instance()->unregistry('view');
        Entity::db_release();

        CacheFactory::reset();

        unset($this->request);
        unset($this->response);
    }
}
