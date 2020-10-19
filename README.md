PHP micro framework - v0.5.1

一个PHP微服务框架, 支持自定义系统组件. 本框架还在不断完善中, 基本核心功能已经完成90%
- 依赖: swoole, composer, php7.3
- 作者: kelezyb

## Features
* 高性能
* 异步, 基于协程
* 依赖注入
* RPC服务支持

## Quick Start
初始化
Lark框架大部分的配置采用注解的方式, 框架依赖composer来管理包.

## 依赖注入
@InjectService  注入开发者提供服务
@Inject 注入系统(beans)中对象

## Controller
每个Controller必须继承与BaseController, 有3个注解来表达注释相关操作. 

@Controller 表示控制器注解, 是路由的前缀
@Route      是方法注解, 对于路由的后缀
@Response   是Http请求返回注解, 表示返回的数据类型, 有json, raw, template

BaseController有request和response对象, 一个用来对请求输入参数读取, 一个为响应输出相关操作
本框架暂时无session操作. 一般用Token的方式替代

Demo:

```php
<?php
use Lark\Routing\BaseController;

use Lark\Annotation\Controller;
use Lark\Annotation\Route;
use Lark\Annotation\Response;
use Lark\Annotation\InjectService;
/**
 * Class TestController
 * @package Controller
 * @Controller(path="/test")
 * @Bean
 */
class TestController extends BaseController
{
    /**
     * @var UserService
     * @InjectService           #该注入自动注入一个Proxy类, 对应变量名为相对应服务, 具体可以参考服务
     */
    private $UserService;

    /**
     * @Route(path="/index")
     * @Response(type="json")
     */
    public function index() {
        $key = $this->UserService->create('menmen_' . mt_rand(1, 20), mt_rand(14, 100));

        return ['code' => 400, 'insertId' => $key];
    }

    /**
     * @Route(path="/test")
     * @Response(type="template", data="index")  #index代表模板文件, 返回值对应模板中的变量
     */
    public function test()
    {
        return [];
    }
}
```

    对应地址

    http://host:port/test/index
    http://host:port/test/test
    小技巧: 用命令php lark.php router 可以看到系统中相关路由表信息


## Command
命令行组件, 每个命令行组件需要继承于Lark\Command\Command类, 该类是一个抽象类, 需要实现registry方法和execute方法, registry是用来注册命令行参数的. 需要返回一个Lark\Command\CommandDescribe对象, 他需要提供模块名称, 命令名称, 和命令行描述.
如果该命令行需要注入服务相关组件, 需要加上@Bean注解, 其他的则和控制器雷同.
execute方法是具体命令执行的方法, 返回是对应命令行的退出码. 必须是有符号的int值. 不返回则为0
执行命令的方法是: ./bin/lark 模块名:命令名称 --参数. 
具体细节可以输入 ./bin/lark help查看

```php
<?php
use Lark\Annotation\Bean;
use Lark\Annotation\InjectService;
use Lark\Command\Command;
use Lark\Command\CommandDescribe;

/**
 * Class DemoCommand
 * @package App\Commands
 * @Bean
 */
class DemoCommand extends Command
{
    /**
     * @var UserService
     * @InjectService
     */
    private $UserService;

    public function registry(): CommandDescribe
    {
        return new CommandDescribe('test', 'demo', 'This is test:demo command');
    }

    public function execute()
    {
        $this->line("test demo mesdsasge");
        $this->block('Test Demo command', ['this is info', ' sdfghsdfh']);
        
        return 1;
    }
}
```


## Service
服务是一组业务逻辑的集合, 我们把相关的代码集成在一起, 减少控制器的业务逻辑降低系统的耦合性.
本身他并没有什么特殊性的地方. 主要还是用来写数据操作的一些业务逻辑
```php
<?php
use App\Entitys\UserEntity;
use Lark\Annotation\Bean;

/**
 * User Service
 * @package App\Services
 * @Bean
 */
class UserService
{
    /**
     * 查找用户数据
     */
    public function get($id)
    {
        return UserEntity::find($id);
    }

    /**
     * 创建用户
     */
    public function create($name, $age, $enable=1)
    {
        $user = new UserEntity();
        $user->setName($name);
        $user->setEnable($enable);
        $user->setAge($age);
        $user->setCreated(time());

        return $user->save();
    }
}
```
 EventManager::Instance()->trigger(new UserLoginEvent(['id' => 200]));
## Event
事件模式是一种经过了充分测试的可靠机制，是一种非常适用于解耦的机制，分别存在以下 3 种角色：

事件(Event) 是传递于应用代码与 监听器(Listener) 之间的通讯对象
监听器(Listener) 是用于监听 事件(Event) 的发生的监听对象
事件管理器(EventManager) 是用于触发 事件(Event) 和管理 监听器(Listener) 与 事件(Event) 之间的关系的管理者对象

用通俗易懂的例子来说明就是，假设我们存在一个 UserService::register() 方法用于注册一个账号，在账号注册成功后我们可以通过事件调度器触发 UserRegistered 事件，由监听器监听该事件的发生，在触发时进行某些操作，比如发送用户注册成功短信，在业务发展的同时我们可能会希望在用户注册成功之后做更多的事情，比如发送用户注册成功的邮件等待，此时我们就可以通过再增加一个监听器监听 UserRegistered 事件即可，无需在 UserService::register() 方法内部增加与之无关的代码。

定义一个事件, 他主要的作用是事件传递中需要传递的对象封装:
```php
<?php
namespace App\Listeners\Event;

use Lark\Event\Event;

/**
 * Class UserRegisteredEvent
 * @package App\Listeners\Event
 */
class UserRegisteredEvent implements Event
{
    private $user;

    /**
     * UserRegisteredEvent constructor.
     * @param $user
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    public function getName()
    {
        return 'UserLogin';
    }
}
```

定义一个监听器:
```php
<?php
namespace App\Listeners;


use App\Listeners\Event\UserRegisteredEvent;
use Lark\Event\EventManager;
use Lark\Event\Listener;
use Swoole\Coroutine;


/**
 * Class UserRegisteredListener
 * @package App\Listeners
 */
class UserRegisteredListener implements Listener
{

    public function listen(): array
    {
        return [
            UserRegisteredEvent::class
        ];
    }

    public function process($event)
    {
//        Coroutine::sleep(2);
        var_dump('用户登录触发主事件', $event);
    }
}
```

定义系统需要监听的事件列表, 在配置Configs/events.php中
```php
<?php
/**
 * 系统需要监听的事件在此配置
 */
return [
    \App\Listeners\UserRegisteredEvent::class,
];
```


然后我们就可以在我们需要触发事件的地方, 直接调用:
```php
EventManager::Instance()->trigger(new UserLoginEvent(['id' => 200, 'name'=>'test', ...]));
```

事件是运行在协程中的代码, 不会阻塞主线程的代码. 运行时间比较长而且不在意结果的操作都可以放到此处(比如发送邮件等). 特别主要, 不要在事件中再次触发事件, 代码有Bug的情况容易造成死循环. 

## Middleware
中间件, 主要用于编织从 请求(Request) 到 响应(Response) 的整个流程, 需要继承 Lark\Middleware\Middleware;
下面的例子实现了请求需要JWT验证, 以及白名单等功能
```php
<?php
namespace App\Middlewares;

use App\Components\JWT\JWTException;
use App\Components\JWT\JwtHandle;
use App\Exceptions\InterfaceException;
use Lark\Middleware\Middleware;
use Lark\Routing\Request;
use Lark\Routing\Response;


/**
 * JWT Middleware
 * @package Middleware
 */
class AuthMiddleware extends Middleware
{
    const DISABLE_URI = [
        '/call/test4',
        '/demo/(.*)',
        '/logger/(.*)'
    ];

    /**
     * @param string $request_uri
     * @return bool
     */
    private function check_request_uri(string $request_uri): bool
    {
        $is_check = false;
        foreach (AuthMiddleware::DISABLE_URI as $pattern) {
            if (preg_match("#^/?" . $pattern . "/?$#", $request_uri, $match)) {
                if ($pattern == $request_uri) {
                    $is_check = true;
                    break;
                } else {
                    $is_check = true;
                    continue;
                }
            }
        }

        return $is_check;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed|void
     * @throws InterfaceException
     */
    public function handle(Request $request, Response $response)
    {
        $request_uri = $request->server('request_uri');
        if (!$this->check_request_uri($request_uri)) {
            $token =  $request->header('authorization');
            try {
                $result = (array)JwtHandle::getInstance()->valid_token($token);
                if ($result['exp'] - time() > 600) {
                    $new_token = JwtHandle::getInstance()->refresh_token($result);
                    $response->header('jwt_token', $new_token, false);
                }
            } catch(JWTException $ex) {
                throw new InterfaceException(InterfaceException::JWT_TOKEN_EXCEPTION);
            }
        }
    }
}
```

## Exception
在Lark框架中, 需要返回一些错误数据的时候我们推荐使用异常, 系统会直接返回对应的json数据. 参考例子如下
```php
<?php
namespace App\Exceptions;

use Lark\Core\Exception;
use Lark\Annotation\Message;
use Throwable;

class InterfaceException extends Exception
{
    /**
     * @Message("参数 [%s] 错误啦")
     */
    public const PARAMS_EXCEPTION = 40001;

    /**
     * @Message("其他 [%s] 错误啦")
     */
    public const OTHER_EXCEPTION = 40002;

    /**
     * InterfaceException constructor.
     * @param int $code
     * @param array $data
     * @param Throwable|null $previous
     */
    public function __construct(int $code = 0, array $data=[], Throwable $previous = null)
    {
        /** @var Throwable $previous */
        parent::__construct($code, $data, $previous);
    }
}
```

我们在控制器中直接抛出异常即可, 参考例子如下:
```php
throw new InterfaceException(InterfaceException::PARAMS_EXCEPTION, ['name']);
```


## Entitys
这个是一个简单的ORM模型, 需要定义对于表所有需要的字段, 参考例子(推荐用开发工具生成set,get方法):

```php
<?php
namespace App\Entitys;


use Lark\Annotation\Model\Column;
use Lark\Annotation\Model\Table;
use Lark\Database\Entity;

/**
 * @Table(name="users")         #对应表名称
 */
class UserEntity extends Entity
{
    /**
     * 主键ID
     *
     * @Column(name="uid",pk=true)  #主键
     * @var int
     */
    private $uid;

    /**
     * 用户ID
     *
     * @Column(name="name")         #字段name
     * @var string
     */
    private $name;

    /**
     *
     * @Column(name="age")
     * @var int
     */
    private $age;

    /**
     *
     * @Column(name="created")
     * @var int
     */
    private $created;

    /**
     *
     * @Column(name="enable")
     * @var int
     */
    private $enable;

    /**
     * @return int
     */
    public function getUid(): int
    {
        return $this->uid;
    }

    /**
     * @param int $uid
     */
    public function setUid(int $uid): void
    {
        $this->uid = $uid;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getAge(): int
    {
        return $this->age;
    }

    /**
     * @param int $age
     */
    public function setAge(int $age): void
    {
        $this->age = $age;
    }

    /**
     * @return int
     */
    public function getCreated(): int
    {
        return $this->created;
    }

    /**
     * @param int $created
     */
    public function setCreated(int $created): void
    {
        $this->created = $created;
    }

    /**
     * @return int
     */
    public function getEnable(): int
    {
        return $this->enable;
    }

    /**
     * @param int $enable
     */
    public function setEnable(int $enable): void
    {
        $this->enable = $enable;
    }
}
```

然后我们就可以直接调用, 
```php
#查找单条数据
UserEntity::find($id);

#添加数据
$user = new UserEntity();
$user->setName($name);
$user->setEnable($enable);
$user->setAge($age);
$user->setCreated(time());

$user->save();
```

## Config
系统所有的配置都放到Configs中, 类似于
```php
# host.php
<?php
/**
 * 系统需要监听的事件在此配置
 */
return [
    "host" => "177.16.345.1",
    "port" => 3309
];

//然后我们可以在代码中取得对于配置数据
$host_info = Config::get(null, 'host');
$port = Config::get('port', 'host')
```


## 日志
系统日志系统采用monolog, 我们可以直接在利用注解@Bean在服务中,  @Inject一个系统对象(logger). 其这个对象可以输出日志到Console或者文件中, 具体细节可以参考monolog