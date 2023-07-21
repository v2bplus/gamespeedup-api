<?php

class GameRegionModel extends GameBaseModel
{
    protected $_table = 'region';
    protected $_primary_key = 'id';
    protected $_filed = ['id', 'name','remark','create_time', 'update_time'];
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
        $addArray['remark'] = $info['remark'];
        $addArray['create_time'] = time();
        $this->insert($addArray);

        return $this->lastInsertId();
    }

    public function updateData($post)
    {
        $array = ['update_time' => time()];

        $array['name'] = $post['name'];
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
