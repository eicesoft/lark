<?php
namespace Lark\Loader;

use Lark\Annotation\Component;
use Lark\Annotation\Inject;
use Lark\Core\Kernel;
use Lark\Di\Proxy;
//use Lark\Routing\Response;
use Lark\Di\ReflectionManager;
use ReflectionClass;
use ReflectionMethod;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Lark\Annotation\Controller;
use Lark\Annotation\Route;
use Lark\Annotation\Response;

class ControllerLoader extends Loader
{
    /**
     * @var ControllerLoader
     */
    private static $instance = null;

    /**
     * get static instance
     * @return ControllerLoader
     */
    public static function Instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @var array
     */
    private $components;

    public function __construct()
    {
        $app = Kernel::Instance()->get('app');
        $controller_path = $app->generate('controller');
        
        parent::__construct($controller_path);
    }

    const BASE_CLASS = 'Lark\Routing\ControllerInterface';
    public function load($namespace, $classname)
    {
        $className = $namespace . '\\' . $classname;
        $routes = [];
        try {
            $reflectionClass = ReflectionManager::reflectClass($className);

            if ($reflectionClass->getParentClass() != false && in_array(self::BASE_CLASS, $reflectionClass->getInterfaceNames())) {
                $reader = new AnnotationReader();
                $class_annotation = $reader->getClassAnnotation($reflectionClass, Controller::class);
                if ($class_annotation) {
                    $methods = ReflectionManager::reflectMethods($className);
                    foreach ($methods as $method) {
                        //router info
                        $method_annotation = $reader->getMethodAnnotation($method, Route::class);

                        if ($method_annotation) {
                            if ($class_annotation->path == null) {
                                $key = $method_annotation->path;
                            } else {
                                $key = $class_annotation->path . $method_annotation->path;
                            }

                            $result_annotation = $reader->getMethodAnnotation($method, Response::class);
                            if ($result_annotation) {
                                $response = ['type' => $result_annotation->type, 'data' => $result_annotation->data];
                            } else {
                                $response = ['type' => 'default', 'data' => ''];
                            }
                            $routes[$key] = ['class' => $method->class, 'method' => $method->name, 'response' => $response];
                        }
                    }
                }
            } else {
                //var_dump($className);
            }
        } catch (\ReflectionException $e) {

        } catch (AnnotationException $e) {

        }

        return $routes;
    }
}