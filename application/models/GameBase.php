<?php

class PayBaseModel
{
    public static $_instance;
    public static $_local_instance;
    protected $_table;
    protected $_primary_key = '';
    protected $_filed = [];
    protected $_order = [];

    protected $_default_order = [];
    protected $_queryFiled = [];

    public function __construct()
    {
    }

    public function __clone()
    {
        trigger_error('clone is not allowed!');
    }

    public function __wakeup()
    {
        trigger_error('unserialize is not allowed!');
    }

    public static function getInstance($config = null)
    {
        if (!$config) {
            $config = Yaf_Registry::get('_dbConfig');
        }
        if (!self::$_instance instanceof Medoo\Medoo) {
            self::$_instance = new Medoo\Medoo($config);
        }

        return self::$_instance;
    }

    public static function getLocalInstance($config = null)
    {
        if (!$config) {
            $config = Yaf_Registry::get('_localDbConfig');
        }
        if (!self::$_local_instance instanceof Medoo\Medoo) {
            self::$_local_instance = new Medoo\Medoo($config);
        }

        return self::$_local_instance;
    }

    public function localQuery($sql)
    {
        return self::getLocalInstance()->query($sql);
    }

    public function debug()
    {
        return self::getInstance()->debug();
    }

    public function getErrors()
    {
        return self::getInstance()->error;
    }

    public function last()
    {
        return self::getInstance()->last();
    }

    public function begin()
    {
        self::getInstance()->pdo->beginTransaction();
    }

    public function commit()
    {
        self::getInstance()->pdo->commit();
    }

    public function rollback()
    {
        self::getInstance()->pdo->rollback();
    }

    public function lastInsertId()
    {
        return self::getInstance()->id();
    }

    public function query($sql)
    {
        return self::getInstance()->query($sql);
    }

    public function find($id = '', $column = [])
    {
        $where = [$this->_primary_key => $id];
        if (!$column) {
            $column = '*';
        } else {
            if ('*' != $column) {
                $column = array_intersect($column, $this->_filed);
            }
        }

        return self::getInstance()->get($this->_table, $column, $where);
    }

    public function getInfoById($id = '', $column = [])
    {
        return $this->find($id, $column);
    }

    public function has($where = [], $join = null)
    {
        if (is_null($join)) {
            return self::getInstance()->has($this->_table, $where);
        }

        return self::getInstance()->has($this->_table, $join, $where);
    }

    public function max($column = 'id', $where = null)
    {
        return self::getInstance()->max($this->_table, $column, $where);
    }

    public function insert($data)
    {
        return self::getInstance()->insert($this->_table, $data);
    }

    public function update($data, $where)
    {
        return self::getInstance()->update($this->_table, $data, $where);
    }

    public function replace($columns, $where)
    {
        return self::getInstance()->replace($this->_table, $columns, $where);
    }

    public function delete($where)
    {
        return self::getInstance()->delete($this->_table, $where);
    }

    public function count($where, $join = null)
    {
        if (is_null($where)) {
            $count = self::getInstance()->count($this->_table, '*');
        } else {
            if (is_null($join)) {
                $count = self::getInstance()->count($this->_table, '*', $where);
            } else {
                $count = self::getInstance()->count($this->_table, $join, '*', $where);
            }
        }

        return $count;
    }

    public function sum($column, $where, $join = null)
    {
        if (null === $join) {
            return self::getInstance()->sum($this->_table, $column, $where);
        }

        return self::getInstance()->sum($this->_table, $join, $column, $where);
    }

    public function fetchRow($where = [], $column = '*', $join = null)
    {
        if (is_null($join)) {
            return self::getInstance()->get($this->_table, $column, $where);
        }

        return self::getInstance()->get($this->_table, $join, $column, $where);
    }

    public function fetchAll($where = [], $column = '*', $join = null)
    {
        if (is_null($join) || empty($join)) {
            return self::getInstance()->select($this->_table, $column, $where);
        }

        return self::getInstance()->select($this->_table, $join, $column, $where);
    }

    public function getPaginate($column = '*', $where = null, $join = null, $curr = 1, $pagesize = 20)
    {
        if (is_numeric($where)) {
            $curr = $where;
            $where = null;
            if (is_numeric($join)) {
                $pagesize = $join;
            }
            $join = null;
        }
        if (is_numeric($join)) {
            $pagesize = $curr;
            $curr = $join;
            $join = null;
        }
        if (empty($join)) {
            $join = null;
        }
        $count = $this->count($where, $join);
        if (0 == $count) {
            return [
                'total_page' => 0,
                'rows' => 0,
                'items' => [],
            ];
        }
        if (0 == $pagesize) {
            $pagesize = 0;
        }

        $totalPage = ceil($count / $pagesize * 1.0);
        $from = ($curr - 1) * $pagesize;
        $where['LIMIT'] = [$from, $pagesize];
        if (!isset($where['ORDER'])) {
            $where['ORDER'] = $this->_default_order;
        }
        $items = $this->fetchAll($where, $column, $join);

        return [
            'last_page' => $totalPage,
            'per_page' => $pagesize,
            'current_page' => $curr,
            'rows' => $count + 0,
            'items' => $items,
        ];
    }

    public function getPaginateList($column = '*', $where = null, $join = null, $curr = 1, $pagesize = 20)
    {
        if (is_numeric($where)) {
            $curr = $where;
            $where = null;
            if (is_numeric($join)) {
                $pagesize = $join;
            }
            $join = null;
        }
        if (is_numeric($join)) {
            $pagesize = $curr;
            $curr = $join;
            $join = null;
        }

        $count = $this->count($where, $join);
        if (0 == $count) {
            return [
                'total_page' => 0,
                'rows' => 0,
                'items' => [],
            ];
        }
        if (0 == $pagesize) {
            $pagesize = 0;
        }
        $totalPage = ceil($count / $pagesize * 1.0);
        $from = ($curr - 1) * $pagesize;
        $where['LIMIT'] = [$from, $pagesize];
        if (!isset($where['ORDER'])) {
            if (!$this->_order) {
                if ($this->_default_order) {
                    $where['ORDER'] = $this->_default_order;
                }
            }
            $fileds = array_intersect(array_keys($this->_order), $this->_filed);
            if ($fileds) {
                $filed = array_shift($fileds);
                $where['ORDER'] = [$filed => $this->_order[$filed]];
            }
        }
        $items = $this->fetchAll($where, $column, $join);

        return [
            'total_page' => $totalPage,
            'rows' => $count + 0,
            'items' => $items,
        ];
    }

    public function getSelectList($where = [], $column = [])
    {
        if (!isset($where['ORDER'])) {
            if ($this->_default_select_order) {
                $where['ORDER'] = $this->_default_select_order;
            } elseif ($this->_default_order) {
                $where['ORDER'] = $this->_default_order;
            }
        }
        if (!$column) {
            $column = $this->_default_select_column;
        }

        return $this->fetchAll($where, $column);
    }

    public static function queryLog()
    {
        return self::getInstance()->log();
    }

    public function tableName()
    {
        return $this->_table;
    }

    public function getQueryFiled()
    {
        return $this->_queryFiled;
    }

    public function getInfoByWhere($where, $column = '*')
    {
        return $this->fetchRow($where, $column);
    }
}
