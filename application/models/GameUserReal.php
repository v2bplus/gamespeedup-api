<?php

class GameUserRealModel extends GameBaseModel
{
    protected $_table = 'user_real';
    protected $_primary_key = 'id';
    protected $_filed = ['id', 'user_id', 'real_name', 'gender', 'id_card_number', 'status', 'content', 'create_time', 'update_time', 'audit_time'];
    protected $_order = [];
    protected $_default_order = ['id' => 'DESC'];

    public function updateData($post = [], $id)
    {
        $array = [
            'update_time' => time(),
        ];
        if (isset($post['audit_time'])) {
            $array['audit_time'] = $post['audit_time'];
        }

        if (isset($post['real_name'])) {
            $array['real_name'] = $post['real_name'];
        }

        if (isset($post['content'])) {
            $array['content'] = $post['content'];
        }

        if (isset($post['remark'])) {
            $array['remark'] = $post['remark'];
        }

        if (!$array) {
            return false;
        }

        return $this->update($array, [
            'id' => $id,
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
