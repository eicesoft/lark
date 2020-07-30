<?php
namespace Lark\Client\Consul;


use Lark\Core\Kernel;
use Swlib\Http\ContentType;
use Swlib\Saber;

/**
 * Class Client
 * @package Lark\Client\Consul
 * @method get($path)
 * @method put($path, $params)
 * @method delete($path)
 * @method post($path, $params)
 */
class Client
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    public function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
        $uri = "http://{$host}:{$port}";

        $this->client = Kernel::Instance()->newInstance(\GuzzleHttp\Client::class, [[
            'base_uri' => $uri,
            'timeout' => 2.0,
            'headers' => [
//                'Content-Type' => 'application/json'
            ]
        ]]);
        
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \Lark\Core\Exception
     */
    public function __call($name, $arguments)
    {
        return Kernel::Instance()->call($this->client, $name, $arguments, true);
    }
}