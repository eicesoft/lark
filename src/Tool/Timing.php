<?php

namespace Lark\Tool;


class Timing
{
    private static $instance;

    /**
     * @return Timing
     */
    public static function Instance(): Timing
    {
        if (null == self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @var array
     */
    private $times;

    private function __construct()
    {
        $this->times = [
            'start' => microtime(true)
        ];
    }

    public function add(string $label)
    {
        $this->times[$label] = microtime(true);
    }

    public function __toString()
    {
        $time_dur = [];
        $start = 0;
        foreach ($this->times as $label => $time) {
            if ($start == 0) {
                $time_dur[] = "{$label};dur=0";
            } else {
                $diff_time = ($time - $start) * 1000;
                $time_dur[] = "{$label};dur={$diff_time}";
            }
            $start = $time;
        }
//        var_dump($this->times);

        return join(', ', $time_dur);
    }
}