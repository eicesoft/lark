<?php


namespace Lark\Command;


use Lark\Core\Lark;
use Lark\Tool\Str;

/**
 * Class Output
 * @package Lark\Command
 */
class Output
{
    private $output;
    public function __construct()
    {
        $this->output = $this->openOutput();
    }

    private function openOutput()
    {
        return @fopen('php://stdout', 'w') ?: fopen('php://output', 'w');
    }

    public function write(string $message, bool $new_line=true, Style $style=null)
    {
        if ($style !== null) {
            $message = $style->format($message);
        }

        fwrite($this->output, $message);
        if ($new_line) {
            fwrite($this->output, "\n");
        }
    }
    
    public function block(string $title, array $descs)
    {
        $all = array_merge([$title], $descs);
        $len = max(array_map('strlen', $all));
        $spacd_size = abs(100 - ceil($len * 2));
//        echo $len, ',', $spacd_size,"\n";
        $max = $len + $spacd_size;
        $this->write(str_repeat('=', $max) );
        $len = Str::len($title);
        $this->write("|" . str_repeat(' ', ($max - $len) / 2 - 1), false);
        $this->write($title, false, new Style(Style::INFO));
        $this->write(str_repeat(' ', ceil(($max - $len) / 2 - 1)) . "|" );
        foreach($descs as $desc) {
            $len = strlen($desc);

            $this->write("|" . str_repeat(' ', ($max - $len) / 2 - 1), false);
            $this->write($desc, false, new Style(Style::DEBUG));
            $this->write(str_repeat(' ', ceil(($max - $len) / 2 - 1)) . "|" );
        }
        $this->write(str_repeat('=', $max) );
    }

    public function close()
    {
        fclose($this->output);
    }
}