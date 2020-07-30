<?php

namespace Lark\Pool;


use Co\Channel;
use Lark\Core\Config;
use Lark\Core\TSingleton;
use Lark\Event\EventManager;
use Lark\Event\Local\PoolEvent;

/**
 * Class ObjectPool
 * @package Lark\Pool
 * @author kelezyb
 */
class ObjectPool
{
    use TSingleton;

    /**
     * 最大活跃数
     * @var int
     */
    public $maxActive = 100;

    /**
     * 最多可空闲数
     * @var int
     */
    public $maxIdle = 10;

    /**
     * 拨号器
     * @var DialerInterface
     */
    protected $dialer;

    /**
     * 连接队列
     * @var Channel
     */
    protected $queue;

    /**
     * 活跃连接集合
     * @var array
     */
    protected $actives = [];

    /**
     * AbstractObjectPool constructor.
     * @param array $config
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct()
    {
        $pool_config = Config::get(get_called_class(), 'pool');
        if ($pool_config) {
            $this->maxActive = $pool_config['maxActive'] ?? 100;
            $this->maxIdle = $pool_config['maxIdle'] ?? 10;
        }

        if (class_exists('Channel')) {
            $this->queue = new Channel($this->maxIdle);
        } else {
            $this->queue = new class {
                public function stats()
                {
                    return ['queue_num' => 0];
                }

                public function push($obj)
                {
                    return true;
                }
            };
        }
    }

    /**
     * 创建连接
     * @return object
     */
    protected function createConnection()
    {
        $closure = function () {
            $connection = $this->dialer->dial();
            $connection->pool = $this;
            return $connection;
        };
        $id = spl_object_hash($closure);
        $this->actives[$id] = '';
        try {
            $connection = call_user_func($closure);
        } finally {
            unset($this->actives[$id]);
        }
        return $connection;
    }

    /**
     * 借用连接
     * @return object
     */
    public function borrow()
    {
        if ($this->getIdleNumber() > 0 || $this->getTotalNumber() >= $this->maxActive) {
            // 队列有连接，从队列取
            // 达到最大连接数，从队列取
            $type = 'borrow';
            $connection = $this->pop();
        } else {
            // 创建连接
            $type = 'create';
            $connection = $this->createConnection();
        }
        //触发事件
        EventManager::Instance()->trigger(new PoolEvent($type, $this));
        // 登记, 队列中出来的也需要登记，因为有可能是 discard 中创建的新连接
        $id = spl_object_hash($connection);
        $this->actives[$id] = ''; // 不可保存外部连接的引用，否则导致外部连接不析构
        // 返回
        return $connection;
    }

    /**
     * 归还连接
     * @param $connection
     * @return bool
     */
    public function return(object $connection)
    {
        $id = spl_object_hash($connection);
        // 判断是否已释放
        if (!isset($this->actives[$id])) {
            return false;
        }
        EventManager::Instance()->trigger(new PoolEvent('return', $this));

        // 移除登记
        unset($this->actives[$id]); // 注意：必须是先减 actives，否则会 maxActive - maxIdle <= 1 时会阻塞
        // 入列
        return $this->push($connection);
    }

    /**
     * 丢弃连接
     * @param $connection
     * @return bool
     */
    public function discard(object $connection)
    {
        $id = spl_object_hash($connection);
        // 判断是否已丢弃
        if (!isset($this->actives[$id])) {
            return false;
        }
        EventManager::Instance()->trigger(new PoolEvent('discard', $this));
        // 移除登记
        unset($this->actives[$id]); // 注意：必须是先减 actives，否则会 maxActive - maxIdle <= 1 时会阻塞
        // 入列一个新连接替代丢弃的连接
        $result = $this->push($this->createConnection());
        // 返回
        return $result;
    }

    /**
     * 获取连接池的统计信息
     * @return array
     */
    public function stats()
    {
        return [
            'total' => $this->getTotalNumber(),
            'idle' => $this->getIdleNumber(),
            'active' => $this->getActiveNumber(),
        ];
    }

    /**
     * 放入连接
     * @param $connection
     * @return bool
     */
    protected function push($connection)
    {
        // 解决对象在协程外部析构导致的: Swoole\Error: API must be called in the coroutine
        if (class_exists('Coroutine')) {
            if (\Swoole\Coroutine::getCid() == -1) {
                return false;
            }
        }
        if ($this->getIdleNumber() < $this->maxIdle) {
            return $this->queue->push($connection);
        }
        return false;
    }

    /**
     * 弹出连接
     * @return mixed
     */
    protected function pop()
    {
        return $this->queue->pop();
    }

    /**
     * 获取队列中的连接数
     * @return int
     */
    protected function getIdleNumber()
    {
        $count = $this->queue->stats()['queue_num'];
        return $count < 0 ? 0 : $count;
    }

    /**
     * 获取活跃的连接数
     * @return int
     */
    protected function getActiveNumber()
    {
        return count($this->actives);
    }

    /**
     * 获取当前总连接数
     * @return int
     */
    protected function getTotalNumber()
    {
        return $this->getIdleNumber() + $this->getActiveNumber();
    }
}