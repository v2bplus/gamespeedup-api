<?php

class GamePaymentInfoModel extends GameBaseModel
{
    protected $_table = 'payment_info';
    protected $_primary_key = 'id';
    protected $_filed = ['id', 'user_id', 'order_id', 'order_no', 'pay_amount', 'currency_code', 'pay_type', 'trade_number', 'status', 'create_time', 'update_time', 'return_msg', 'remark', 'field1', 'field2'];
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
        $addArray['order_id'] = $info['order_id'];
        $addArray['order_no'] = $info['order_no'];
        $addArray['pay_amount'] = $info['pay_amount'];
        $addArray['pay_type'] = $info['pay_type']??0;
        $addArray['create_time'] = time();
        $this->insert($addArray);

        return $this->lastInsertId();
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
