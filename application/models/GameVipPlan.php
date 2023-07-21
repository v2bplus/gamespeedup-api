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
                'name' => $name,
                'id[!]' => $id,
            ];
        } else {
            $where = [
                'name' => $name,
            ];
        }

        return $this->has($where, $join = null);
    }

    public function addData($userInfo = [])
    {
        $addArray = [];
        $addArray['user_name'] = $userInfo['user_name'];
        $addArray['mobile'] = $userInfo['mobile'] ?? null;
        $addArray['password'] = $userInfo['password'];

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
        if (isset($post['password'])) {
            $array['password'] = $post['password'];
        }

        if (isset($post['group_id'])) {
            $array['group_id'] = $post['group_id'];
        }

        if (isset($post['remark'])) {
            $array['remark'] = $post['remark'];
        }

        if (isset($post['expire_time'])) {
            $array['expire_time'] = $post['expire_time'];
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
