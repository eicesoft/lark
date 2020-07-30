<?php

namespace Lark\Command;


/**
 * Class Progress
 * @package Lark\Command
 * @author kelezyb
 */
class Progress
{
    /**
     * @var int
     */
    private $value;

    /**
     * @var int
     */
    private $max;

    /**
     * Progress constructor.
     * @param int $max
     */
    public function __construct($max = 100)
    {
        $this->value = 0;
        $this->max = $max;
    }

    public function setRate(int $val = 0)
    {
        if ($val > $this->max) {
            $val = $this->max;
        }

        $this->value = $val;

        return $this;
    }

    /**
     *
     */
    public function render()
    {
        $downlen = ceil(50 * ($this->value / $this->max));
        $spacelen = 50 - $downlen;
        $rate = ceil(intval(($this->value / $this->max) * 100));
        $style = new Style(196, 196);
        $buf = '[';

        if ($rate != 100) {
            $buf .= $style->format(str_repeat('#', $downlen));
            $buf .= $style->setColor(160)->setBgColor(160)->format('#');
            $buf .= $style->setColor(88)->setBgColor(88)->format('#');
            $buf .= $style->setColor(52)->setBgColor(52)->format('#');
        } else {
            $buf .= $style->format(str_repeat('#', $downlen + 3));
        }

        $buf .= str_repeat(' ', $spacelen) . '] ' . $rate . "%" . "\r";
        echo $buf;

        if ($rate == 100)
        {
            echo "\r\n";
        }
    }
}