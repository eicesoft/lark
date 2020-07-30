<?php
namespace Lark\Event\Local;


use Lark\Event\Event;

class ExceptionEvent implements Event
{
    /**
     * @var \Exception
     */
    private $exception;

    /**
     * ExceptionEvent constructor.
     * @param \Exception $exception
     */
    public function __construct(\Exception $exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    public function getName()
    {
        return 'Exception';
    }
}