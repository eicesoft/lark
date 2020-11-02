<?php
namespace Lark\Core;

use Illuminate\Database\Capsule\Manager;
use Lark\Loader\CommandLoader;
use Lark\Service\HttpService;
use Lark\Service\RpcService;
use Lark\Service\Service;

define('DS', DIRECTORY_SEPARATOR);

if (class_exists('\\Co')) {
    \Co::set(['hook_flags' => SWOOLE_HOOK_ALL]);//真正的hook所有类型，包括CURL
}

/**
 * Class Lark
 * @package Lark\Core
 */
class Lark
{
    const VERSION = '0.5.1';

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $mode;

    public function __construct($config)
    {
        $this->config = $config;
        $this->_init();
        Kernel::Instance()->registry('app', $this);
    }

    private function initEnv()
    {
        $env_file = BASE_ROOT . '/.env';
        if (is_readable($env_file))
        {
            $envs = parse_ini_file($env_file);
            foreach($envs as $key => $val)
            {
                putenv("{$key}={$val}");
            }
        }
    }

    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    public function get($key, $default=null)
    {
        return isset($this->config[$key]) ? $this->config[$key] : $default;
    }

    public function generate($module)
    {
        return sprintf("%s%s%s%s%s", $this->config['root'], DS, $this->config['service'], DS, $this->config['paths'][$module]);
    }

    public function app_path()
    {
        return sprintf("%s%s%s", $this->config['root'], DS, $this->config['service']);
    }

    private function _init()
    {
        if (!defined('BASE_ROOT')) {
            define('BASE_ROOT', $this->config['root']);
        }

        if (!defined('APP_ROOT')) {
            define('APP_ROOT', $this->config['root'] . DS . $this->config['service']);
        }

        if (!defined('REQUEST_TIME')) {
            define('REQUEST_TIME', $_SERVER['REQUEST_TIME_FLOAT']);
        }

        if (isset($this->config['timezone'])) {
            date_default_timezone_set($this->config['timezone']);
        } else {
            date_default_timezone_set('UTC');
        }

        if ($this->config['debug']) {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
        } else {
            ini_set("display_errors", 0);
            error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
        }

        $this->initEnv();

        ErrorHandler::registry();
        $this->registry_database();

        return $this;
    }

    private function registry_database()
    {
        $manager = new Manager;

        $manager->addConnection([
            'driver'    => 'mysql',
            'host'      => env('DB_HOST'),
            'port'      => env('DB_PORT'),
            'database'  => env('DB_DATABASE'),
            'username'  => env('DB_USERNAME'),
            'password'  => env('DB_PASSWORD'),
            'charset'   => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_CHARSET_CODE', 'utf8mb4_unicode_ci'),
            'prefix'    => '',
        ]);

        $manager->setAsGlobal();
        $manager->bootEloquent();
    }

    /**
     * @throws Exception
     * @throws \ReflectionException
     */
    public function run($mode=HttpService::class)
    {
        $this->mode = $mode;
        $cfg_key = '';
        if ($mode == RpcService::class) {
            $cfg_key = 'rpc';
        } else {
            $cfg_key = 'server';
        }

        $configs = $this->get($cfg_key);
        $kernel = Kernel::Instance();
        $service = Kernel::Instance()->newInstance($mode, $configs);
        $kernel->call($service, 'run');
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @throws \ReflectionException
     */
    public function cli()
    {
        $process = Kernel::Instance()->newInstance(\Lark\Command\CommandProcess::class);
        $code = intval($process->execute());
        exit($code);
    }
}