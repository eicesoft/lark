<?php
declare(strict_types=1);
namespace Lark\Command\Base;


use Lark\Command\Command;
use Lark\Command\CommandDescribe;
use Lark\Core\Lark;
use Lark\Service\HttpService;
use Lark\Service\RpcService;

class StartCommand extends Command
{
    public function registry(): CommandDescribe
    {
        return new CommandDescribe(null, 'start', 'Start lark service');
    }

    public function execute()
    {
        $config = include('config.php');
        $rpc = $this->getOpt('rpc');
//        $rpc_http = $this->getOpt('rpc-http');
        $mode = HttpService::class;
        if ($rpc) {
            $mode = RpcService::class;
        }
        
        try {
            $lark = new Lark($config);
            $lark->run($mode);
        } catch (Exception $ex) {
            \Lark\Core\Console::error($ex->getMessage());
        }
    }
}