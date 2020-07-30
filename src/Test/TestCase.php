<?php
namespace Lark\Test;

use Lark\Core\Kernel;
use Lark\Core\Lark;
use Lark\Di\ReflectionManager;
use PHPUnit\Framework\TestCase as BaseTestCase;


class TestCase extends BaseTestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $config = include('config.php');
        $lark = new Lark($config);

        $class = ReflectionManager::reflectClass(get_called_class());
        Kernel::Instance()->inject($class, $this);
        Kernel::Instance()->injectService($class, $this);
    }

    protected function setUp()
    {

    }

    protected function tearDown()
    {

    }
}