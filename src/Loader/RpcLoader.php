<?php
namespace Lark\Loader;


use Doctrine\Common\Annotations\AnnotationReader;
use Lark\Annotation\Controller;
use Lark\Core\Kernel;
use Lark\Di\ReflectionManager;
use Lark\Rpc\RpcContext;

/**
 * Class RpcLoader
 * @package Lark\Loader
 */
class RpcLoader extends Loader
{
    /**
     * @var RpcLoader
     */
    private static $instance = null;

    /**
     * get static instance
     * @return RpcLoader
     */
    public static function Instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $app = Kernel::Instance()->get('app');
        $controller_path = $app->generate('rpc');

        parent::__construct($controller_path);
    }

    /**
     * @param $namespace
     * @param $classname
     * @return array
     * @throws \ReflectionException
     */
    public function load($namespace, $classname)
    {
        $nclassName = $namespace . '\\' . $classname;
        $rpcs = [];
        $kennel = Kernel::Instance();
        /** @var RpcContext $rpc_obj */
        $rpc_obj = $kennel->newInstance($nclassName, []);
        if ($rpc_obj instanceof RpcContext) {
            $reflectionClass = ReflectionManager::reflectClass(get_class($rpc_obj));
            $reader = new AnnotationReader();
            /** @var \Lark\Annotation\Rpc $rpc_annotation */
            $rpc_annotation = $reader->getClassAnnotation($reflectionClass, \Lark\Annotation\Rpc::class);
            if ($rpc_annotation) {
                $rpcs[$rpc_annotation->name] = [
                    'object' => $rpc_obj,
                    'class' => $nclassName,
                ];
            }
        }

        return $rpcs;
    }
}