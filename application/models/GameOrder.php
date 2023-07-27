<?php

class GameOrderModel extends GameBaseModel
{
    protected $_table = 'order';
    protected $_primary_key = 'id';
    protected $_filed = ['id', 'user_id', 'order_no', 'order_type', 'total_amount', 'invite_user_id', 'commission_balance', 'pay_type', 'status', 'create_time', 'update_time', 'pay_time'];
    protected $_order = [];
    protected $_default_order = ['id' => 'DESC'];

    public function checkExist($where)
    {
        return $this->has($where);
    }

    public function addData($info = [])
    {
        $addArray = [];
        $addArray['user_id'] = $info['user_id'];
        $addArray['order_no'] = $info['order_no'];
        $addArray['order_type'] = $info['order_type'];
        $addArray['total_amount'] = $info['total_amount'];
        $addArray['invite_user_id'] = $info['invite_user_id']??0;
        $addArray['pay_type'] = $info['pay_type']??0;
        $addArray['create_time'] = time();
        $this->insert($addArray);

        return $this->lastInsertId();
    }

    public function updateData($post = [], $id)
    {
        $array = [];
        if (isset($post['total_amount'])) {
            $array['total_amount'] = $post['total_amount'];
        }
        if (isset($post['pay_type'])) {
            $array['pay_type'] = $post['pay_type'];
        }
        if (isset($post['status'])) {
            $array['status'] = $post['status'];
        }

        if (!$array) {
            return false;
        }
        $array['uptime'] = [
            'update_time' => time()
        ];

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
