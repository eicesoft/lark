<?php
namespace Lark\Middleware;

use Lark\Routing\Request;
use Lark\Routing\Response;

/**
 * Middleware
 * @package Lark\Routing
 * @author kelezyb
 */
abstract class Middleware
{
    /**
     * request start
     * @param Request $request
     * @return mixed
     */
    public abstract function handle(Request $request, Response $response);
}