<?php

class GameUserVipModel extends GameBaseModel
{
    protected $_table = 'user_vip';
    protected $_primary_key = 'id';
    protected $_filed = ['id', 'user_id', 'time_num', 'status', 'create_time', 'start_time', 'end_time'];
    protected $_order = [];
    protected $_default_order = ['user_vip.id' => 'DESC'];
    protected $_orderFiled = [
        // 'add_date' => 'users.add_date',
    ];

    public function checkExist($where)
    {
        return $this->has($where);
    }

    public function addData($info = [])
    {
        $addArray = [];
        $addArray['user_id'] = $info['user_id'];
        $addArray['status'] = $info['status']??-1;
        $addArray['time_num'] = $info['time_num']??0;
        $this->insert($addArray);
        return $this->lastInsertId();
    }

    public function updateData($post)
    {
        $array = [];
        $array['status'] = $post['status'];
        if (isset($post['start_time'])) {
            $array['start_time'] = $post['start_time'];
        }
        if (isset($post['time_num'])) {
            $array['time_num'] = $post['time_num'];
        }
        if (isset($post['end_time'])) {
            $array['end_time'] = $post['end_time'];
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
        $where['ORDER'] = $this->_default_order;
        $where += $condition;

        if (!empty($order)) {
            $sort = $order['sort'] ?? null;
            $sortDir = $order['sortDir'] ?? null;
            if (in_array($sort, $this->_order)) {
                $where['ORDER'] = [$this->_orderFiled[$sort] => $sortDir];
            }
        }
        $column = [$this->_table.'.id', $this->_table.'.user_id', 'user.nickname', $this->_table.'.status', $this->_table.'.start_time', $this->_table.'.end_time'];
        $join = [
            '[>]user' => [
                'user_id' => 'id',
            ],
        ];

        return $this->getPaginate($column, $where, $join, $page, $pageSize);
    }
}
