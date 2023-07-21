<?php

class GameUserVipModel extends GameBaseModel
{
    protected $_table = 'game';
    protected $_primary_key = 'id';
    protected $_filed = ['id', 'name', 'alias', 'type', 'logo_url', 'cover_img_url', 'rule_id', 'region_ids', 'status', 'create_time', 'update_time'];
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
