<?php
namespace Lark\Middleware;

use Lark\Core\Console;
use Lark\Core\Kernel;
use Lark\Exception\RequestException;
use Lark\Routing\Request;
use Lark\Routing\Response;
use Lark\View\View;

class RouterMiddleware extends Middleware
{
    /**
     * @var array
     */
    private $routes;

    public function getRouters()
    {
        return $this->routes;
    }

    /**
     * RouterMiddleweare constructor.
     * @param array $routes
     */
    public function __construct()
    {
        $loader = \Lark\Loader\ControllerLoader::Instance();
        $this->routes = $loader->getLoadMetas();
    }

    private function match($request)
    {
        $path = $request->server('path_info');

        $match_controller = null;
        foreach ($this->routes as $pattern => $controller) {
            if (preg_match("#^/?" . $pattern . "/?$#", $path, $match)) {
                if ($pattern == $path) {
                    $match_controller = $controller;
                    break;
                } else {
                    $match_controller = $controller;
                    continue;
                }
            }
        }

        return $match_controller;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed|void
     * @throws \Lark\Core\Exception
     * @throws \ReflectionException
     */
    public function handle(Request $request, Response $response)
    {
        $controller_meta = $this->match($request);
        if ($controller_meta !== null) {
            Console::debug("Http request [%s:%s] => %s:%s", [$request->server('request_method'),
                $request->server('path_info'), $controller_meta['class'], $controller_meta['method']]);
            try {
                $controller = Kernel::Instance()->newInstance($controller_meta['class'], [$request, $response]);
                $data = Kernel::Instance()->call($controller, $controller_meta['method']);
                $response->status(200);
                switch ($controller_meta['response']['type']) {
                    case 'xml':
                        Kernel::Instance()->call($controller, 'handleMidlewares');
                        $root = $controller_meta['response']['data'] != '' ? $controller_meta['response']['data'] : "root";
                        $response->header('Content-Type', 'application/xml')->end(xml_encode($data, $root));
                        break;
                    case 'template':
                        /** @var View $template */
                        $template = bean('template');
                        Kernel::Instance()->call($controller, 'handleMidlewares');

                        $content = $template->render($controller_meta['response']['data'], $data);
                        $response->header('Content-Type', 'text/html')->end($content);
                        break;
                    case 'raw':
                        Kernel::Instance()->call($controller, 'handleMidlewares');
                        $response->end($data);
                        break;
                    case 'json':
                    default:
                        Kernel::Instance()->call($controller, 'handleMidlewares');
//                            call_user_func_array([$controller, 'handleMidlewares'], []);
                        $response->header('Content-Type', 'application/json')->end(json_encode($data));
                        break;
                }
                unset($controller);
            } catch (RequestException $ex) {
                Console::error($ex->getMessage());
                $response->header('Content-Type', 'application/json')->end(json_encode(
                    [
                        'code' => $ex->getCode(),
                        'message' => $ex->getMessage()
                    ]
                ));
            }
        } else {
            Console::warn("Router not exists => %s", [$request->server('path_info')]);
            $response->status(404)->end(<<<EOT
<html>
    <head><title>404 page not found</title></head>
    <body>
    <h1>HTTP error, 404 page not found</h1>
    <h4>Router not exists {$request->server('path_info')}</h4>
    </body>
</html>
EOT);
        }
    }
}