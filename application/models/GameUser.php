<?php

class GameUserModel extends GameBaseModel
{
    protected $_table = 'user';
    protected $_primary_key = 'id';
    protected $_filed = ['id', 'nickname', 'mobile', 'email', 'uuid', 'plan_id', 'invite_user_id', 'group_id', 'php_password', 'create_time', 'update_time', 'real_status', 'status', 'last_login_time', 'remark'];
    protected $_order = [];
    protected $_default_order = ['id' => 'DESC'];

    public function checkMobile($email, $id = null)
    {
        if ($id) {
            $where = [
                'mobile' => $email,
                'id[!]' => $id,
            ];
        } else {
            $where = [
                'mobile' => $email,
            ];
        }

        return $this->has($where, $join = null);
    }

    public function addData($userInfo = [])
    {
        $addArray = [];
        if (isset($userInfo['id'])) {
            $addArray['id'] = $userInfo['id'];
        }
        if (isset($userInfo['invite_user_id'])) {
            $addArray['invite_user_id'] = $userInfo['invite_user_id'];
        }
        $addArray['mobile'] = $userInfo['mobile'] ?? null;
        $addArray['nickname'] = $userInfo['nickname'] ?? null;
        $addArray['email'] = $userInfo['email'] ?? null;
        $addArray['php_password'] = $userInfo['php_password'];

        if (isset($userInfo['uuid'])) {
            $addArray['uuid'] = $userInfo['uuid'];
        }

        if (isset($userInfo['expire_time'])) {
            $addArray['expire_time'] = $userInfo['expire_time'];
        }

        if (isset($userInfo['last_login_time'])) {
            $addArray['last_login_time'] = $userInfo['last_login_time'];
        }
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
        if (isset($post['php_password'])) {
            $array['php_password'] = $post['php_password'];
        }

        if (isset($post['email'])) {
            $array['email'] = $post['email'];
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
