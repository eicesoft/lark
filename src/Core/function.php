<?php
/*
 * core helper function
 */

use Monolog\Logger;

/**
 * object encode to xml string
 * @param mixed $data
 * @param string $root
 * @return string
 */
function xml_encode($data, $root='root')
{
    $xml = ["<?xml version=\"1.0\" encoding=\"UTF-8\"?>"];
    $xml[] = "<{$root}>";
    foreach ($data as $key => $val) {
        if (is_array($val)) {
            $xml[] = "<{$key}>" . xml_encode($val) . "</{$key}>";
        } else {
            $xml[] = "<{$key}>{$val}</{$key}>";
        }
    }
    $xml[] = "</{$root}>";
    return join('', $xml);
}

/**
 * get bean object
 * @param string $name
 * @return object
 */
function bean($name)
{
    return \Lark\Di\Bean::Instance()->create($name);
}

/**
 * create instance
 * @param string $className
 * @param array $params
 * @throws ReflectionException
 */
function _N(string $className, array $params)
{
    \Lark\Core\Kernel::Instance()->newInstance($className, $params);
}

/**
 * get singleton object
 * @param string $name
 * @return mixed
 */
function singleton($name)
{
    return \Lark\Di\Bean::Instance()->singleton($name);
}

/**
 * get config item
 * @param string $key
 * @param string $module
 * @return mixed
 */
function _C($key, $module="")
{
    return \Lark\Core\Config::get($key, $module);
}


/**
 * registry object to core
 * @param string $name
 * @param object $object
 */
function _R($name, $object)
{
    \Lark\Core\Kernel::Instance()->registry($name, $object);
}


/**
 * get object to core
 * @param string $name
 */
function _G($name)
{
    return \Lark\Core\Kernel::Instance()->get($name);
}

/**
 * get database model
 * @param string $table
 * @param string $database
 * @return \Lark\Database\Model
 */
function _M($table, $database='database')
{
    return new \Lark\Database\Model($table, $database);
}

function env($key, $default = null)
{
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }
    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'empty':
        case '(empty)':
            return '';
        case 'null':
        case '(null)':
            return;
    }
    if (($valueLength = strlen($value)) > 1 && $value[0] === '"' && $value[$valueLength - 1] === '"') {
        return substr($value, 1, -1);
    }
    return $value;
}


/**
 * console display exception
 * @param Exception $ex
 */
function display_exception(Exception $ex)
{
    $err_style = new \Lark\Command\Style(\Lark\Command\Style::ERROR);
    echo $err_style->format("Exception: " . get_class($ex) . ' => ' . $ex->getMessage() . "({$ex->getCode()})\n");
    echo $err_style->format("File: {$ex->getFile()}, Line: {$ex->getLine()}\n");
    echo $err_style->setColor(\Lark\Command\Style::WARN)->format("Traces:\n");
    foreach ($ex->getTrace() as $trace) {
        $file = $trace['file'] ?? 'empty';
        $line = $trace['line'] ?? '0';
        $class = $trace['class'] ?? 'null';
        $function = $trace['function'] ?? 'null';
        echo $err_style->setColor(\Lark\Command\Style::WARN)->format("    File: {$file}:{$line} => {$class}::{$function}\n");
    }
}


/**
 * @param $message
 * @param array $content
 * @param int $level
 */
function logger($message, $content=[], $level=Logger::DEBUG)
{
    /** @var \Lark\Logger\LoggerAdapter $logger */
    $logger = bean('logger');
    $logger->log($message, $content, $level);
}

if (!function_exists('app')) {
    /**
     * @return \Lark\Core\Lark
     */
    function app()
    {
        return \Lark\Core\Kernel::Instance()->get('app');
    }
}
