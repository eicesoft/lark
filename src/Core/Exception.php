<?php
namespace Lark\Core;


use Doctrine\Common\Annotations\AnnotationReader;
use Hyperf\Utils\Str;
use Lark\Annotation\Inject;
use Lark\Annotation\Message;
use Lark\Di\Proxy;
use Lark\Di\ReflectionManager;

/**
 * Class Exception
 * @package Lark\Core
 * @author kelezyb
 */
class Exception extends \Exception
{
    const LOADERE_CLASS = 40021;
    const CONFIG_FILE_NOFOUND = 40002;
    const REMOTE_CONTAINER_ERROR = 40100;
    const DATABASE_ERROR = 50100;

    private $messages;

    protected $data;


    /**
     * Exception constructor.
     */
    public function __construct($code, $data=[], Throwable $previous = null)
    {
        $this->data = $data;

        $this->messages = [];

        $reader = new AnnotationReader();
        $constants = ReflectionManager::reflectConstants(get_called_class());
        /** @var \ReflectionClassConstant $constant */
        foreach ($constants as $constant) {
            $value = $constant->getValue();
            $docComment = $constant->getDocComment();
            if ($docComment) {
                $this->messages[$value] = $this->parse($docComment);
            }
        }
        if (isset($this->messages[$code])) {
            $message = vsprintf($this->messages[$code]['Message'], $data);
        } else {
            $message = 'Unknown error.';
        }

        parent::__construct($message, $code, $previous);
    }

    protected function parse(string $doc)
    {
        $pattern = '/\\@(\\w+)\\(\\"(.+)\\"\\)/U';
        if (preg_match_all($pattern, $doc, $result)) {
            if (isset($result[1], $result[2])) {
                $keys = $result[1];
                $values = $result[2];

                $result = [];
                foreach ($keys as $i => $key) {
                    if (isset($values[$i])) {
                        $result[$key] = $values[$i];
                    }
                }
                return $result;
            }
        }

        return [];
    }


    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}