<?php

class GameOrderModel extends GameBaseModel
{
    protected $_table = 'payment_info';
    protected $_primary_key = 'id';
    protected $_filed = ['id', 'user_id', 'order_id', 'order_no', 'pay_amount', 'currency_code', 'pay_type', 'trade_number', 'status', 'create_time', 'update_time', 'return_msg', 'remark', 'field1', 'field2'];
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
