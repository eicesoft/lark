<?php
namespace Lark\Command\Base\Gengrate;


use Lark\Command\Command;
use Lark\Command\CommandDescribe;
use Lark\Command\Style;
use Lark\Core\Kernel;
use Lark\Core\Lark;
use Lark\Routing\BaseController;


/**
 * Class GenerateControllerCommand
 * @package Lark\Command\Base\Gengrate
 * @author kelezyb
 */
class GenerateControllerCommand extends Command
{
    public function registry(): CommandDescribe
    {
        return new CommandDescribe('gen', 'controller', "Generate new controller");
    }

    public function execute()
    {
        $args = $this->params['args'];
        $opts = $this->params['opts'];
        if ( $this->getArgLength() < 2 && $this->getOpt('name') == null) {
            $this->line("Usage:\n\tphp bin/lark gen:controller ControllerName\n\tphp bin/lark gen:controller --name ControllerName");
            exit(1);
        }
        $controller_name = $this->isOpt('name') ? $this->getOpt('name') : $this->getArg(1);
        $controller = ucfirst($controller_name) . 'Controller';
        /** @var Lark $app */
        $app = _G('app');
        $controller_path = $app->generate('controller');
        $doc = <<<PHP
<?php
namespace App\Controllers;

use Lark\Routing\BaseController;
use Lark\Annotation\Controller;
use Lark\Annotation\Route;
use Lark\Annotation\Response;

/**
 * {$controller} class
 *
 * @Controller(path="/{$controller_name}")
 * @package Controllers
 * @author xx
 */
class {$controller} extends BaseController
{
    /**
     * @Route(path="/index")
     * @Response(type="json")
     */
    public function index() {
        return [];
    }
}
PHP;
        $controller_file = $controller_path . DS . $controller . '.php';
        if (!file_exists($controller_file)) {
            file_put_contents($controller_file, $doc);
            $this->line("Controller {$controller} create success.", new Style(Style::INFO));
        } else {
            $this->line("Controller file {$controller_file} exists, create fails.", new Style(Style::ERROR));
        }
    }
}