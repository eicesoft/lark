<?php

namespace Lark\Command\Base\Gengrate;


use Lark\Command\Command;
use Lark\Command\CommandDescribe;
use Lark\Command\Style;
use Lark\Database\Db;

class GenerateEntityCommand extends Command
{
    public function registry(): CommandDescribe
    {
        return new CommandDescribe('gen', 'entity', 'Generate new entity class');
    }

    public function execute()
    {
        $args = $this->params['args'];
        $opts = $this->params['opts'];
        if ($this->getArgLength() < 2 && $this->getOpt('name') == null) {
            $this->line("Usage:\n\tphp bin/lark gen:entity EntityName\n\tphp bin/lark gen:entity --name EntityName");
            exit(1);
        }

        if ($this->getOpt('table') == null) {
            $this->line("Usage:\n\tphp bin/lark gen:entity EntityName --table TableName");
            exit(1);
        }

        $entity_name = $this->isOpt('name') ? $this->getOpt('name') : $this->getArg(1);
        $entity = ucfirst($entity_name) . 'Entity';

        $table = $this->isOpt('table') ? $this->getOpt('table') : null;
        $columns = $this->getColumns($table);
//        var_dump($columns);
        $app = _G('app');
        $entity_path = $app->generate('entity');
        $column_docs = [];
        foreach ($columns as $column) {
            $name = $column['name'];
            $ucname = ucfirst($name);
            #echo $name . '|' . $ucname . PHP_EOL;
            if ($column['pk'] == 'true') {
                $pkinfo = ',pk=true';
            } else {
                $pkinfo = '';

            }
            $column_doc = <<<DOC
    /**
     * 
     * @Column(name="{$name}"{$pkinfo})
     * @var mixed
     */
    private \${$name};

    /**
     * @return mixed
     */
    public function get{$ucname}()
    {
        return \$this->{$name};
    }

    /**
     * @param mixed \${$name}
     */
    public function set{$ucname}(\${$name}): void
    {
        \$this->{$name} = \${$name};
    }
DOC;
            $column_docs[] = $column_doc;
        }
        $column_all_doc = join("\n", $column_docs);
        $doc = <<<PHP
<?php
namespace App\Entitys;


use Lark\Annotation\Model\Column;
use Lark\Annotation\Model\Table;
use Lark\Database\Entity;

/**
 * @Table(name="$table")
 */
class $entity extends Entity
{
{$column_all_doc}
}
PHP;

        $entity_file = $entity_path . DS . $entity . '.php';
        if (!file_exists($entity_file)) {
            file_put_contents($entity_file, $doc);
            $this->line("Entity {$entity} create success.", new Style(Style::INFO));
        } else {
            $this->line("Entity file {$entity_file} exists, create fails.", new Style(Style::ERROR));
        }
    }

    private function getColumns($table)
    {
        /** @var Db $db */
        $db = bean('db');
        try {
            $datas = $db->database('database')->query('SHOW COLUMNS FROM ' . $table, []);
        } catch (\Exception $ex) {
            $this->line("Table {$table} doesn't exist.", new Style(Style::ERROR));
            die();
        }

        $columns = [];
        if ($datas) {
            foreach ($datas as $data) {
                list($type) = explode('(', $data['Type']);
                $columns[] = [
                    'name' => $data['Field'],
                    'pk' => $data['Key'] == 'PRI' ? 'true' : 'false',
                    'type' => $type
                ];
            }
        }

        return $columns;
    }
}