<?php

namespace Lark\Routing;


use Doctrine\Common\Annotations\AnnotationReader;
use Lark\Annotation\Inject;
use Lark\Annotation\InjectService;
use Lark\Core\Config;
use Lark\Core\Context;
use Lark\Core\Kernel;
use Lark\Di\Proxy;
use Lark\Middleware\Middleware;

/**
 * Controller base class
 * @package Lark\Routing
 * @author kelezyb
 */
class BaseController extends Context implements ControllerInterface
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var array
     */
    protected $middlewares;

    /**
     * Controller constructor.
     * @param Request $request
     * @param Response $response
     */
    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;

//        $this->initMiddleware();
    }

    public function fetch($controller, $method)
    {
        $controller_obj = Kernel::Instance()->newInstance($controller, [$request, $response]);
        return Kernel::Instance()->call($controller_obj, $method);
    }

    public function __destruct()
    {

    }
}