<?php
declare(strict_types=1);

namespace Lark\Event\Local;

use Lark\Event\Event;
use Swoole\Http\Request;


/**
 * Request Event
 * @package Lark\Event\Local
 */
class RequestEvent implements Event
{
    /**
     * @var Request
     */
    private $request;

    /**
     * RequestEvent constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }


    public function getName()
    {
        return 'Request';
    }
}