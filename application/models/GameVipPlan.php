<?php

class GameVipPlanModel extends GameBaseModel
{
    protected $_table = 'vip_plan';
    protected $_primary_key = 'id';
    protected $_filed = ['id', 'plan_name', 'money', 'day_time', 'gift_day_time', 'content', 'show', 'sort', 'create_time', 'update_time'];
    protected $_order = [];
    protected $_default_order = ['id' => 'DESC'];

    public function checkName($name, $id = null)
    {
        if ($id) {
            $where = [
                'plan_name' => $name,
                'id[!]' => $id,
            ];
        } else {
            $where = [
                'plan_name' => $name,
            ];
        }

        return $this->has($where, $join = null);
    }

    public function addData($info = [])
    {
        $addArray = [];
        $addArray['plan_name'] = $info['plan_name'];
        $addArray['money'] = $info['money'];
        $addArray['day_time'] = $info['day_time'];

        $addArray['gift_day_time'] = $info['gift_day_time'] ?? 0;
        $addArray['content'] = $info['content'];
        $addArray['show'] = $info['show'];

        $addArray['create_time'] = time();
        $addArray['update_time'] = time();
        $this->insert($addArray);

        return $this->lastInsertId();
    }

    public function updateData($post = [], $id)
    {
        $array = [
            'update_time' => time(),
        ];
        if (isset($post['plan_name'])) {
            $array['plan_name'] = $post['plan_name'];
        }
        if (isset($post['money'])) {
            $array['money'] = $post['money'];
        }
        if (isset($post['day_time'])) {
            $array['day_time'] = $post['day_time'];
        }
        if (isset($post['gift_day_time'])) {
            $array['gift_day_time'] = $post['gift_day_time'];
        }
        if (isset($post['expire_time'])) {
            $array['expire_time'] = $post['expire_time'];
        }
        if (isset($post['show'])) {
            $array['show'] = $post['show'];
        }
        if (!$array) {
            return false;
        }

        return $this->update($array, [
            'id' => $id,
        ]);
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
