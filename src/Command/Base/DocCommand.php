<?php
declare(strict_types=1);

namespace Lark\Command\Base;


use Doctrine\Common\Annotations\AnnotationReader;
use Lark\Annotation\Controller;
use Lark\Annotation\Parameter;
use Lark\Annotation\Response;
use Lark\Annotation\Route;
use Lark\Command\Command;
use Lark\Command\CommandDescribe;
use Lark\Core\Config;
use Lark\Di\ReflectionManager;
use Lark\Loader\ControllerLoader;
use Symfony\Component\Yaml\Yaml;

/**
 * 生成 OpanApi 文档
 * @package Lark\Command\Base
 */
class DocCommand extends Command
{
    const OPENAPI_VERSION = '3.0.0';

    public function registry(): CommandDescribe
    {
        return new CommandDescribe('doc', 'build', "Build openapi xml doc.");
    }

    public function execute()
    {
        $output = $this->getOpt('o', null);
        $type = $this->getOpt('t', 'json');
        $infos = ControllerLoader::Instance()->getLoadMetas();
        $docs = [];
        $docs['openapi'] = self::OPENAPI_VERSION;
        $paths = [];
        foreach ($infos as $route_path => $info) {
            $className = $info['class'];
            $class = ReflectionManager::reflectClass($className);
            $reader = new AnnotationReader();
            $controller_anntation = $reader->getClassAnnotation($class, Controller::class);
            $method = $class->getMethod($info['method']);
            $anntations = $reader->getMethodAnnotations($method);
            $path = [];
            $route_anntation = null;
            $parameters = [];
            $response = [];
            foreach ($anntations as $anntation) {
                if ($anntation instanceof Route) {
                    if ($anntation->tag) {
                        $path['tags'] = [$anntation->tag];
                    }
                    $path['summary'] = $anntation->summary ?? '';
                    $path['description'] = $anntation->desc ?? '';
                    $route_anntation = $anntation;
                } elseif ($anntation instanceof Parameter) {
                    $parameters[] = [
                        'name' => $anntation->name,
                        'in' => $anntation->in,
                        'description' => $anntation->desc,
                        'required' => $anntation->required,
                        'schema' => ['type' => $anntation->type]
                    ];
                } elseif ($anntation instanceof Response) {
                    $response = [
                        'description' => $anntation->desc,
                    ];
                }
            }
            if ($parameters) {
                $path['parameters'] = $parameters;
            }

            if ($response) {
                $path['responses'] = [
                    200 => $response
                ];
            }

            $paths[$route_path] = [
                $route_anntation->method => $path
            ];
        }
        $docs['paths'] = $paths;
        $ext_info = Config::get(null, 'swagger');
        if ($info) {
            $docs += $ext_info;
        }

        if ($type == 'json') {
            $content = json_encode($docs);
        } else {
            $content = Yaml::dump($docs, 8,2);
        }
        if ($output == null) {
            echo $content;
        } else {
            file_put_contents($output, $content);
            $this->line("Output file {$output} is success");
        }
    }
}