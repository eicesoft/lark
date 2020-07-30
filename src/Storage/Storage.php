<?php

namespace Lark\Storage;

/**
 * Class Storage
 * @package Lark\Storage
 * @author
 */
class Storage
{
    const FILTER_DIR = 0;
    const FILTER_FILE = 1;
    const FILTER_ALL = 2;

    /**
     * @var string
     */
    private $path;

    private $disable_dirs = [
    ];

    /**
     * Storage constructor.
     * @param $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @param string $dir
     * @param int $mode
     * @return array
     */
    private function get_files($dir, $mode)
    {
        $dir = realpath($dir);
        $dh = opendir($dir);
        if (!$dh) {
            return [];
        }
        $dirs = [];
        if ($mode == self::FILTER_DIR || $mode == self::FILTER_ALL) {
            $dirs[] = $dir;
        }
        while (($file = readdir($dh)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $full = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($full)) {
                $skip = false;

                foreach ($this->disable_dirs as $disable_dir) {
                    if (strpos($full, $disable_dir) !== false) {
                        $skip = true;
                        break;
                    }
                }
                if (!$skip) {
                    $dirs = array_merge($dirs, $this->get_files($full, $mode));
                }
            } else {
                if ($mode == self::FILTER_FILE || $mode == self::FILTER_ALL) {
                    $dirs[] = $full;
                }
            }
        }

        closedir($dh);
        return $dirs;
    }

    /**
     * @param int $mode
     * @return array
     */
    public function scan($mode = self::FILTER_DIR)
    {
        return $this->get_files($this->path, $mode);
    }

    /**
     * @param array $disable_dirs
     */
    public function setDisableDirs(array $disable_dirs): void
    {
        $this->disable_dirs = $disable_dirs;
    }
}