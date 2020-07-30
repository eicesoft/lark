<?php


namespace Lark\Service;

use Lark\Core\Kernel;
use Lark\Event\EventManager;
use Lark\Event\Local\ExceptionEvent;
use Lark\Event\Local\RequestEvent;
use Lark\Routing\Dispacther;
use Lark\Routing\FPMResponse;
use Lark\Routing\Response;
use Swoole\Http\Request;

class FCGIService
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    public function __construct()
    {
        $this->initRequest();
        $this->response = new Response(new FPMResponse());
    }

    private function initRequest()
    {
        $this->request = new Request();
        $this->request->get = $_GET;
        $this->request->post = $_POST;
        $this->request->server = array_change_key_case($_SERVER, CASE_LOWER);
        ;
        $this->request->cookie = $_COOKIE;
        $this->request->files = $_FILES;
        $this->request->header = array_change_key_case(getallheaders(), CASE_LOWER);
    }

    public function run()
    {
        EventManager::Instance()->trigger(new RequestEvent($this->request));
        $this->onRequest($this->request, $this->response);
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    public function onRequest($request, $response)
    {
        $kernel = Kernel::Instance();
        /** @var Dispacther $dispacher */
        $dispacher = $kernel->newInstance(Dispacther::class, [$request, $response]);

        try {
            ob_start();
            EventManager::Instance()->trigger(new RequestEvent($request));
            $kernel->call($dispacher, 'execute');
            unset($dispacher);
        } catch (\Exception $ex) {
            EventManager::Instance()->trigger(new ExceptionEvent($ex));
//            display_exception($ex);
            $dispacher->exception($ex);
        } finally {
            $data = ob_get_contents();
            ob_clean();
            echo $data;
        }
    }
}
