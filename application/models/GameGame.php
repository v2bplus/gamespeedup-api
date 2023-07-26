<?php

class GameGameModel extends GameBaseModel
{
    protected $_table = 'game';
    protected $_primary_key = 'id';
    protected $_filed = ['id', 'name', 'alias', 'type', 'logo_url', 'cover_img_url', 'rule_id', 'region_ids', 'status', 'create_time', 'update_time'];
    protected $_order = [];
    protected $_default_order = ['id' => 'DESC'];

    public function checkName($name,$type, $id = null)
    {
        if ($id) {
            $where = [
                'name' => $name,
                'type' => $type,
                'id[!]' => $id,
            ];
        } else {
            $where = [
                'name' => $name,
                'type' => $type,
            ];
        }

        return $this->has($where);
    }

    public function addData($userInfo = [])
    {
        $addArray = [];
        $addArray['name'] = $userInfo['name'];
        $addArray['alias'] = $userInfo['alias'] ?? null;
        $addArray['type'] = $userInfo['type'];
        $addArray['logo_url'] = $userInfo['logo_url'];
        $addArray['cover_img_url'] = $userInfo['cover_img_url'];
        $addArray['region_ids'] = $userInfo['region_ids'];
        $addArray['status'] = $userInfo['status'];

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
        if (isset($post['name'])) {
            $array['name'] = $post['name'];
        }
        if (isset($post['alias'])) {
            $array['alias'] = $post['alias'];
        }
        if (isset($post['type'])) {
            $array['type'] = $post['type'];
        }
        if (isset($post['logo_url'])) {
            $array['logo_url'] = $post['logo_url'];
        }
        if (isset($post['cover_img_url'])) {
            $array['cover_img_url'] = $post['cover_img_url'];
        }
        if (isset($post['region_ids'])) {
            $array['region_ids'] = $post['region_ids'];
        }
        if (isset($post['status'])) {
            $array['status'] = $post['status'];
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
