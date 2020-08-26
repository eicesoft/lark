<?php
namespace Lark\Database;


use Controller\InterfaceException;
use Doctrine\Common\Annotations\AnnotationReader;
use Lark\Annotation\Model\Column;
use Lark\Annotation\Model\Table;
use Lark\Core\Kernel;
use Lark\Core\Lark;
use Lark\Di\Proxy;
use Lark\Di\ReflectionManager;


/**
 * Class Entity
 * @package Lark\Database
 * @author kelezyb
 */
class Entity implements \JsonSerializable
{
    /**
     * @var string
     */
    private $table;

    /**
     * @var array
     */
    private $columns=[];

    /**
     * @var array
     */
    private $data=[];

    private $isNew = true;

    /**
     * @var array
     */
    private $fill_data=[];

    /**
     * @var \ReflectionClass
     */
    private $reflection;

    /**
     * @var \ReflectionProperty[]
     */
    private $reflection_properties;


    /**
     * @var Db
     */
    private $db;

    /**
     * @var mixed|object|string
     */
    private $pk_info;

    private static $dbs = [];

    public static function db_release()
    {
        /**
         * @var string $key
         * @var Db $db
         */
        foreach(self::$dbs as $key => &$db) {
            $db->__return();
            unset($db);
        }
        self::$dbs = [];
    }

    /**
     * Entity constructor.
     * @throws \ReflectionException
     */
    public function __construct()
    {
        $reader = new AnnotationReader();
        $this->reflection = ReflectionManager::reflectClass(get_called_class());
        $this->reflection_properties = ReflectionManager::reflectPropertys(get_called_class(), \ReflectionMethod::IS_PRIVATE);
        $class_anntation = $reader->getClassAnnotation($this->reflection, \Lark\Annotation\Model\Table::class);
        $this->table = $class_anntation->name;
        $database = $class_anntation->database;
        if (empty($class_anntation->database)) {
            $database = Kernel::Instance()->get('app')->get('default_database', 'database');
        }

//        if (!Kernel::Instance()->isregistry('db')) {
//            $this->db = bean("db");
//            $this->db->database($database);
//            Kernel::Instance()->registry('db', $this->db);
//        } else {
//            $this->db = Kernel::Instance()->get('db');
//        }

        if (!isset(self::$dbs[$database])) {
            $this->db = bean("db");
            $this->db->database($database);
            self::$dbs[$database] = $this->db;
        } else {
            $this->db = self::$dbs[$database];
        }

        foreach ($this->reflection_properties as $property) {
            $property_anntation = $reader->getPropertyAnnotation($property, Column::class);
            if ($property_anntation) {
                if ($property_anntation->pk) {
                    $this->pk_info = $property_anntation;
                }
                $this->columns[$property->name] = ['anntation' => $property_anntation, 'property' => $property];
            }
        }
    }

    /**
     * 填充数据
     *
     * @param array $data
     */
    public function fill($data) {
        if (!empty($data)) {
            $this->isNew = false;

            if ($data) {
                foreach ($data as $key => $val) {
                    if (isset($this->columns[$key])) {
                        $this->data[$key] = $val;
                        $this->fill_data[$key] = $val;
                        $property = $this->columns[$key]['property'];
                        $property->setAccessible(true);
                        $property->setValue($this, $val);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * 删除
     * @return int
     */
    public function remove()
    {
        return $this->db->table($this->getTable())->where([$this->pk_info->name . '=?'])->delete([$this->getPk()]);
    }

    /**
     * @param mixed $pk
     */
    private function setPk($pk)
    {
        foreach($this->columns as $key => $column) {
            if ($column['anntation']->pk) {
                $column['property']->setAccessible(true);
                $val = $column['property']->setValue($this, $pk);
                break;
            }
        }
    }

    /**
     * @return |null
     */
    private function getPk()
    {
        $pk = null;
        foreach($this->columns as $key => $column) {

            $column['property']->setAccessible(true);
            $val = $column['property']->getValue($this);
            if ($column['anntation']->pk) {
                $pk = $val;
                break;
            }
        }

        return $pk;
    }

    /**
     * save data
     * @return int
     */
    public function save() {
        if ($this->isNew) {
            $this->isNew = false;
            foreach($this->columns as $key => $column) {
                $column['property']->setAccessible(true);
                $val = $column['property']->getValue($this);
                if ($val !== null) {
                    $this->data[$key] = $val;
                    $old_val = isset($this->fill_data[$key]) ? $this->fill_data[$key] : null;

                    if ($val != $old_val) {
                        $this->data[$key] = $val;
                    }
                }
            }
            $this->fill_data = $this->data;
            $pk = $this->db->table($this->getTable())->insert($this->data, true);
            $this->setPk($pk);
            return $pk;
        } else {
            $data = [];
            $pk = null;
            foreach($this->columns as $key => $column) {
                $column['property']->setAccessible(true);
                $val = $column['property']->getValue($this);
                if ($column['anntation']->pk) {
                    $pk = $val;
                }
                if ($val !== null) {
                    $this->data[$key] = $val;
                    $old_val = isset($this->fill_data[$key]) ? $this->fill_data[$key] : null;

                    if ($val !== $old_val) {
                        $data[$key] = $val;
                    }
                }
            }

            if (empty($data)) {
                return 0;
            } else {
                return $this->db->table($this->getTable())->where([$this->pk_info->name  . '=' . $pk])->update($data);
            }
        }
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param mixed $pk
     * @param string $field
     * @return $this
     */
    private function findOne($pk, $field)
    {
        $data = $this->db->table($this->getTable())->fields($field)->where([$this->pk_info->name  . '=?'])->one([$pk]);
        return $this->fill($data);
    }

    /**
     * @param array $wheres
     * @param null $sort
     * @param null $limit
     * @param bool $format
     * @return array|mxied|null
     */
    private function findAll($wheres, $params=[], $sort=null, $limit=null, $format=false)
    {
        $datas = $this->db->table($this->getTable())->where($wheres)->sort($sort)->limit($limit)->get($params);
        if ($format) {
            $result = [];
            foreach ($datas as $data) {
                $m = new self();
                $m->fill($data);
                $result[] = $datas;
            }
            return $result;
        } else {
            return $datas;
        }
    }

    public function toArray()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->data);
    }

    public function __toString()
    {
        return "{
            Data: {$this->toJson()}
        }";
    }

    /**
     * @param mixed $pk
     * @param string $field
     * @return mixed
     */
    public static function find($pk, $field='*')
    {
        $class = static::class;
        /** @var Entity $self */
        $self = new $class();
        return $self->findOne($pk, $field);
    }

    /**
     * @param array $wheres
     * @param array $params
     * @param array $sort
     * @param array $limit
     * @param bool $format
     * @return array|mxied|null
     */
    public static function finds($wheres, $params, $sort=null, $limit=null, $format=false)
    {
        $class = static::class;
        /** @var Entity $self */
        $self = new $class();
        return $self->findAll($wheres, $params, $sort, $limit, $format);
    }

    /**
     * @return Db
     */
    public static function db()
    {
        $class = static::class;
        /** @var Entity $self */
        $self = new $class();
        return $self->db->table($self->getTable());
    }

    /**
     * @param string $sql
     * @param array $params
     * @return mixed
     */
    public static function query($sql, $params)
    {
        $class = static::class;
        /** @var Entity $self */
        $self = new $class();
        return $self->db->query($sql, $params);
    }

    /**
     * @param string $sql
     * @param array $params
     * @param bool $lastId
     * @return int
     */
    public static function execute(string $sql, array $params, bool $lastId = false)
    {
        $class = static::class;
        /** @var Entity $self */
        $self = new $class();
        return $self->db->execute($sql, $params, $lastId);
    }

    public static function count($where=[], $params=[])
    {
        $class = static::class;
        /** @var Entity $self */
        $self = new $class();
        return $self->db->table($self->getTable())->where($where)->count($params);
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}