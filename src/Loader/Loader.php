<?php

namespace Lark\Loader;


abstract class Loader
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    protected $load_metas = [];

    /**
     * @return array
     */
    public function getLoadMetas()
    {
        return $this->load_metas;
    }

    /**
     * Loader constructor.
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;

        $fileItems = $this->glob($this->path);
        foreach ($fileItems as $fileItem) {
            $this->load_metas = array_merge($this->load_metas , $this->buildCache($fileItem));
        }
    }

    public abstract function load($namespace, $classname);

    /**
     * build router cache
     * @param string $filename
     * @return array|null
     */
    protected function buildCache($filename)
    {
        $content = file_get_contents($filename);
        $namespace_preg = '/namespace\ (.*)\;/';
        $class_preg = "/class\ (\w+)/";
        if (preg_match($class_preg, $content, $class_matches) && preg_match($namespace_preg, $content, $namespace_matches)) {
            $base_classname = $class_matches[1];
            $namespace_name = $namespace_matches[1];
            include_once $filename;

            return $this->load($namespace_name, $base_classname);
        } else {
            return [];
        }
    }

    /**
     * @param string $path
     * @param string $ext
     * @return array
     */
    protected function glob($path, $ext = '*')
    {
        $fileItem = [];
        foreach (glob($path . DIRECTORY_SEPARATOR . $ext) as $v) {
            $newPath = $v;
            if (is_dir($newPath)) {
                $fileItem = array_merge($fileItem, $this->glob($newPath));
            } else if (is_file($newPath)) {
                $fileItem[] = $newPath;
            }
        }

        return $fileItem;
    }
}