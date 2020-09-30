<?php

namespace Lark\Core;

use Doctrine\Common\Annotations\AnnotationReader;
use Lark\Annotation\Bean;
use Lark\Annotation\Controller;
use Lark\Annotation\Inject;
use Lark\Annotation\InjectService;
use Lark\Annotation\Rpc;
use Lark\Di\Proxy;
use Lark\Di\ReflectionManager;

/**
 * Kernel class
 * @package Lark\Core
 * @author kelezyb
 */
final class Kernel
{
    /**
     * @var Kernel
     */
    private static $instance = null;

    /**
     * get static instance
     * @return Kernel
     */
    public static function Instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @var array
     */
    private $objects;

    /**
     * Kernel constructor.
     */
    public function __construct()
    {
        $this->objects = [];
    }

    /**
     * @param string $name
     * @param mixed $instance
     * @throws KernelException
     */
    public function registry($name, $instance)
    {
        if (isset($this->objects[$name])) {
//            throw new KernelException('', KernelException::REGISTRY_OBJECT);
        }

        $this->objects[$name] = $instance;
    }

    public function isregistry($name)
    {
        return isset($this->objects[$name]);
    }

    /**
     * @param $name
     */
    public function unregistry($name)
    {
        unset($this->objects[$name]);
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function get($name)
    {
        return isset($this->objects[$name]) ? $this->objects[$name] : null;
    }

    /**
     * @param string $className
     * @param array $params
     * @return object
     * @throws \ReflectionException
     */
    public function newInstance($className, $params = [])
    {
        $class = ReflectionManager::reflectClass($className);
        $handler = $class->newInstanceArgs($params);
        $reader = new AnnotationReader();
        $bean_anntation = $reader->getClassAnnotation($class, Bean::class);
        $controller_anntation = $reader->getClassAnnotation($class, Controller::class);
        $rpc_anntation = $reader->getClassAnnotation($class, Rpc::class);

        if ($bean_anntation || $controller_anntation || $rpc_anntation) {  //需要注入系统
            $this->inject($class, $handler);
            $this->injectService($class, $handler);
        }

        return $handler;
    }

    /**
     * inject bean object
     * @param \ReflectionClass $reflect_class
     * @param object $object
     */
    public function inject(\ReflectionClass $reflect_class, object $object)
    {
        $reader = new AnnotationReader();

        $properties = $reflect_class->getProperties(\ReflectionMethod::IS_PRIVATE | \ReflectionMethod::IS_PROTECTED);
        foreach ($properties as $property) {
            $property_anntation = $reader->getPropertyAnnotation($property, Inject::class);

            if ($property_anntation) {
                $property->setAccessible(true);
                $property->setValue($object, \bean($property_anntation->name));
            }
        }
    }

    /**
     * inject service
     * @param \ReflectionClass $reflect_class
     * @param object $object
     */
    public function injectService(\ReflectionClass $reflect_class, object $object)
    {
        $reader = new AnnotationReader();
        $properties = $reflect_class->getProperties(\ReflectionMethod::IS_PRIVATE);
        foreach ($properties as $property) {
            $property_anntation = $reader->getPropertyAnnotation($property, InjectService::class);
            if ($property_anntation) {
                $property->setAccessible(true);
                $property->setValue($object, new Proxy('\App\Services\\' . $property->name));
            }
        }
    }

    /**
     * Call object is method
     * @param object $obj
     * @param string $method
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function call(object $obj, string $method, array $params = [], $skipTest = false)
    {
        if (method_exists($obj, $method) || $skipTest) {
            return call_user_func_array([$obj, $method], $params);
        } else {
            $msg = sprintf("Object %s method [%s] is undefined", get_class($obj), $method);
            throw new \Exception($msg);
        }
    }
}