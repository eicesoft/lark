<?php
namespace Lark\Routing;


use Lark\Core\Console;
use Lark\Core\Kernel;
use Lark\Exception\RequestException;
use Lark\View\View;

/**
 * Class Router
 * @package Lark\Routing
 */
class Router
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

    /**
     * @param string $pathinfo
     * @return mixed|string
     */
    private function split_path_info($pathinfo)
    {
        if (strpos($pathinfo, '?') === false)
        {
            return $pathinfo;
        } else {
            list($pathinfo) = explode('?', $pathinfo, 2);
//            var_dump($x);
            return $pathinfo;
        }
    }

    private function match($request)
    {
        $path = $this->split_path_info($request->server('path_info'));
        $match_controller = null;
        foreach ($this->routes as $pattern => $controller) {
            if (preg_match("#^/?" . $pattern . "/?$#", $path, $match)) {
                if ($match) {
                    array_shift($match);

                    $match_controller = $controller;
                    $match_controller['params'] = $match;
                    break;
                } else {
                    $match_controller = $controller;
                    $match_controller['params'] = [];
                    continue;
                }
            }
        }

        return $match_controller;
    }

    private function check_content_type($response, $content_type)
    {
        $headers = $response->getHeaders();

        $is_set_content_type = false;
        foreach ($headers as $header) {
            if ($header[0] == 'Content-Type') {
                $is_set_content_type = true;
            }
        }

        if (!$is_set_content_type) {
            $response->header('Content-Type', $content_type);
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed|void
     * @throws \Lark\Core\Exception
     * @throws \ReflectionException
     */
    public function execute(Request $request, Response $response)
    {
        $controller_meta = $this->match($request);
        if ($controller_meta !== null) {
            Console::debug("Http request [%s:%s] => %s:%s", [$request->server('request_method'),
                $request->server('path_info'), $controller_meta['class'], $controller_meta['method']]);
            try {
                $controller = Kernel::Instance()->newInstance($controller_meta['class'], [$request, $response]);
                $data = Kernel::Instance()->call($controller, $controller_meta['method'], $controller_meta['params']);
                $response->status(200);
                switch ($controller_meta['response']['type']) {
                    case 'xml':
                        $root = $controller_meta['response']['data'] != '' ? $controller_meta['response']['data'] : "root";

                        $this->check_content_type($response, 'application/xml');
                        $response->end(xml_encode($data, $root));
                        break;
                    case 'template':
                        /** @var View $template */
                        $template = bean('template');
                        $this->check_content_type($response, 'text/html');

                        if ($data instanceof ViewResponse) {
                            $content = $template->render($data->getName(), $data->getData());
                            $response->end($content);
                        } else {
                            $content = $template->render($controller_meta['response']['data'], $data);
                            $response->end($content);
                        }
                        break;
                    case 'json':
                        $this->check_content_type($response, 'application/json');
                        $response->end(json_encode($data));
                        break;
                    case 'raw':
                    default:
                        $response->end($data);
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
EOT
            );
        }
    }
}