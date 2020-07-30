<?php
namespace Lark\View;


/**
 * Twig view
 * @package Lark\View
 */
class TwigView extends View
{
    /**
     * @var \Twig_Loader_Filesystem
     */
    private $loader;

    /**
     * @var \Twig_Environment
     */
    private $env;

    private $functions;

    /**
     * TwigView constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        parent::__construct($params);
        $this->functions = [];
    }

    /**
     * add template function
     * @param string $name
     * @param Closure $callback
     * @param array|bool[] $options
     */
    public function addFunction(string $name, \Closure $callback, array $options=["pre_escape"=> false,"preserves_safety"=> false,"is_safe"=> ["html"]])
    {
        if (!isset($this->functions[$name])) {
            $this->functions[$name] = $callback;
            $func = new \Twig_SimpleFunction($name, $callback, $options);
            $this->env->addFunction($func);
        }
    }

    /**
     * add global val
     * @param string $name
     * @param mixed $val
     */
    public function addGlobal(string $name, $val)
    {
        $this->env->addGlobal($name, $val);
    }

    /**
     * @param string $template
     * @param array $data
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function render(string $template, array $data): string
    {
        $template = $this->env->loadTemplate($template . '.twig');
        return $template->render($data);
    }

    public function init(): void
    {
        $this->loader = new \Twig_Loader_Filesystem($this->params['view_path']);
        $this->env = new \Twig_Environment($this->loader, $this->params['options']);
        $this->env->addExtension(new \Twig_Extension_Debug());

        $this->env->addGlobal("server", $_SERVER);
    }
}