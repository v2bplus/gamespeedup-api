<?php

class GameUserGroupModel extends GameBaseModel
{
    protected $_table = 'user_group';
    protected $_primary_key = 'id';
    protected $_filed = ['id', 'name', 'attribute', 'create_time', 'update_time', 'remark'];
    protected $_order = [];
    protected $_default_order = ['id' => 'DESC'];

    public function checkExist($where)
    {
        return $this->has($where);
    }

    public function addData($info = [])
    {
        $addArray = [];
        $addArray['name'] = $info['name'];
        $addArray['attribute'] = $info['attribute'];
        $addArray['create_time'] = time();
        $this->insert($addArray);

        return $this->lastInsertId();
    }

    public function checkName($name, $id = null)
    {
        if ($id) {
            $where = [
                'name' => $name,
                'id[!]' => $id,
            ];
        } else {
            $where = [
                'name' => $name,
            ];
        }

        return $this->has($where, $join = null);
    }

    public function getAll($column = '')
    {
        if (empty($column)) {
            $column = $this->_filed;
        }
        $where = [];
        $where['ORDER'] = ['id' => 'ASC'];

        return $this->fetchAll($where, $column);
    }

    public function updateData($post)
    {
        $array = ['update_time' => time()];

        $array['name'] = $post['name'];
        $array['attribute'] = $post['attribute'];
        if (isset($post['remark'])) {
            $array['remark'] = $post['remark'];
        }

        return $this->update($array, [
            'id' => $post['id'],
        ]);
    }


    public function getList($page, $pageSize, $column = null, $condition = [], $order = [])
    {
        $where = [
            'LIMIT' => $pageSize,
        ];

        $where += $condition;

        $where['ORDER'] = $this->_default_order;
        if (!empty($order)) {
            $sort = $order['sort'] ?? null;
            $sortDir = $order['sortDir'] ?? null;
            if (in_array($sort, $this->_order)) {
                $where['ORDER'] = [$sort => $sortDir];
            }
        }
        if (empty($column) || ('*' == $column)) {
            $column = $this->_filed;
        }

        return $this->getPaginate($column, $where, null, $page, $pageSize);
    }

}
