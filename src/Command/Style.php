<?php
namespace Lark\Command;


/**
 * Class Style
 * @package Lark\Command
 * @author kelezyb
 */
class Style
{
    const DEBUG = 8;
    const DEFAULT = 14;
    const INFO = 2;
    const WARN = 3;
    const ERROR = 1;
    const ALERT = 9;

    private $color;

    private $bgColor;
    
    /**
     * Style constructor.
     * @param $color
     * @param $bgColor
     */
    public function __construct($color, $bgColor=null)
    {
        $this->color = $color;
        $this->bgColor = $bgColor;
    }

    public function format($message)
    {
        $bgColorCode = "";
        if ($this->bgColor != null) {
            $bgColorCode = ";48;5;{$this->bgColor}";
        }
        return sprintf("\e[0;38;5;{$this->color}{$bgColorCode}m%s\e[0m", $message);
    }

    /**
     * @param mixed $color
     */
    public function setColor($color)
    {
        $this->color = $color;
        return $this;
    }

    /**
     * @param int $bgColor
     */
    public function setBgColor(int $bgColor)
    {
        $this->bgColor = $bgColor;
        return $this;
    }
}