<?php

class GameNodeModel extends GameBaseModel
{
    protected $_table = 'node';
    protected $_primary_key = 'id';
    protected $_filed = ['id', 'name', 'region_id', 'region_name', 'protocol', 'host_addr', 'host_port', 'allow_insecure', 'capacity_limit', 'json_values', 'sort', 'status', 'create_time', 'update_time'];
    protected $_order = [];
    protected $_default_order = ['id' => 'DESC'];

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
