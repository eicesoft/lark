<?php
namespace Lark\View;

use Lark\Core\Kernel;

/**
 * View base class
 * @package Lark\View
 * @author kelezyb
 */
abstract class View
{
    /**
     * @var array
     */
    protected $params;

    /**
     * View constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        Kernel::Instance()->registry('view', $this);
        $this->params = $params;
        $this->init();
    }
    public abstract function init(): void;
    public abstract function render(string $template, array $data): string;
}