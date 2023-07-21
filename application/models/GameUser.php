<?php

class GameUserModel extends GameBaseModel
{
    protected $_table = 'user';
    protected $_primary_key = 'id';
    protected $_filed = ['id', 'nickname', 'mobile', 'email', 'uuid', 'plan_id', 'invite_user_id', 'group_id', 'php_password', 'create_time', 'update_time', 'real_status', 'status', 'last_login_time', 'remark'];
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
