<?php

namespace Services\Game;

class Group extends \Service
{
    // 增加用户组
    public static function addGroup($post)
    {
        try {
            $groupModel = new \GameUserGroupModel();
            $name = $post['name'];
            $check = $groupModel->checkName($name);
            if ($check) {
                throw new \Exception('已存在名为: '.$name.'的组');
            }
            $groupInfo = [];
            $groupInfo['name'] = $name;
            $groupInfo['attribute'] = json_encode($post['attribute']);

            $id = $groupModel->addData($groupInfo);
            if ($groupModel->getErrors()) {
                throw new \Exception('添加用户组失败');
            }
            $return = [
                'id' => $id,
            ];

            return [
                'status' => 1,
                'data' => $return,
                'msg' => '',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage(),
            ];
        }
    }

    // 后台使用
    public static function getAllList($page, $pageSize, $order)
    {
        try {
            $where = [];

            $groupModel = new \GameUserGroupModel();
            $column = [];
            $list = $groupModel->getList($page, $pageSize, $column, $where, $order);

            if (!$list) {
                return [
                    'status' => 1,
                    'data' => [],
                ];
            }

            return [
                'status' => 1,
                'data' => $list,
                'msg' => '',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public static function getNameList()
    {
        $groupModel = new \GameUserGroupModel();
        $data = [];
        $list = $groupModel->getAll(['id',  'name']);
        if ($list) {
            foreach ($list as $item) {
                $data[$item['id']] = $item;
            }
        }

        return $data;
    }

    public static function editGroup($post)
    {
        try {
            $groupModel = new \GameUserGroupModel();
            $info = $groupModel->getInfoById($post['id'], ['id', 'name']);
            if (!$info) {
                throw new \Exception('信息不存在');
            }
            $check = $groupModel->checkName($post['name'], $post['id']);
            if ($check) {
                throw new \Exception('已存在名字为: '.$post['name']);
            }

            $data = [];
            $data['id'] = $post['id'];
            $data['name'] = $post['name'];
            $data['attribute'] = json_encode($post['attribute']);

            $groupModel->updateData($data, $post['id']);
            if ($groupModel->getErrors()) {
                throw new \Exception('更新用户组信息失败');
            }

            return [
                'status' => 1,
                'data' => [],
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
