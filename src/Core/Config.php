<?php
namespace Lark\Core;


use Lark\Core\Exception;
use Lark\Core\Kernel;

/**
 * Config
 * @package Lark\Core
 */
class Config
{
    /**
     * @var array
     */
    private static $data = [];

    /**
     * @param string $module
     * @throws KernelException
     */
    private static function load($module) {
        /** @var Lark $app */
        $app = Kernel::Instance()->get('app');
        $app_config = $app->generate('config');
        $config_file = $app_config . DS . $module . '.php';

        if (is_readable($config_file)) {
            self::$data[$module] = require($config_file);
        } else {
            throw new \Exception("config {$config_file} file not found", Exception::CONFIG_FILE_NOFOUND);
        }
    }

    /**
     * @param string $key
     * @param string $module
     * @param MIXED $default
     * @return mixed
     */
    public static function get($key, $module='app', $default=null) {
        if (!isset(self::$data[$module])) {
            self::load($module);
        }

        if ($key == null) {
            return self::$data[$module];
        } else {
            return isset(self::$data[$module][$key]) ? self::$data[$module][$key] : $default;
        }
    }
}