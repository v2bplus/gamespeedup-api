<?php

class GameAdminUserModel extends GameBaseModel
{
    protected $_table = 'admin_user';
    protected $_primary_key = 'id';
    protected $_filed = ['id', 'user_name', 'real_name', 'password', 'create_time', 'update_time', 'last_login_time', 'last_login_ip', 'status', 'remark'];
    protected $_order = [];
    protected $_default_order = ['id' => 'DESC'];

    public function checkExist($where)
    {
        return $this->has($where);
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

    public function checkEmail($email, $id = null)
    {
        if ($id) {
            $where = [
                'email' => $email,
                'id[!]' => $id,
            ];
        } else {
            $where = [
                'email' => $email,
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

        if (!$array) {
            return false;
        }

        return $this->update($array, [
            'id' => $id,
        ]);
    }


    public function del($where)
    {
        return $this->delete($where);
    }

    public function checkPassword($password, $id = null)
    {
        if ($id) {
            $where = [
                'base64_password' => base64_encode($password),
                'id[!]' => $id,
            ];
        } else {
            $where = [
                'base64_password' => base64_encode($password),
            ];
        }

        return $this->has($where, $join = null);
    }

    public function getAll($where = [], $column = '')
    {
        if (empty($column)) {
            $column = $this->_filed;
        }

        return $this->fetchAll($where, $column);
    }
}
