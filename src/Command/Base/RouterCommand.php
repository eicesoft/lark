<?php
namespace Lark\Command\Base;

use Lark\Command\Command;
use Lark\Command\CommandDescribe;
use Lark\Loader\ControllerLoader;
use Lark\Loader\RpcLoader;


/**
 * Router list command
 * @package Lark\Command\Base
 * @author kelezyb
 */
class RouterCommand extends Command
{
    public function registry(): CommandDescribe
    {
        return new CommandDescribe(null, 'router', "List all router info");
    }

    public function execute()
    {
        if ($this->getOpt('rpc')) {
            $infos = RpcLoader::Instance()->getLoadMetas();
            foreach ($infos as $key => $info) {
                $this->line("Rpc Method: [{$key}] \t\t {$info['class']}");
            }
        } else {
            $infos = ControllerLoader::Instance()->getLoadMetas();
            foreach ($infos as $key => $info) {
                $this->line("Router:\t\t{$key} \t\t {$info['class']}:{$info['method']} ({$info['response']['type']})");
            }
        }
    }
}