<?php

namespace Services\Game;

class UserReal extends \Service
{
    public static $status = [
        '0', '1', '2',
    ];

    public static function getAll($page, $pageSize, $order)
    {
        try {
            $where = [];
            $userModel = new \GameUserRealModel();
            $column = [];
            $list = $userModel->getList($page, $pageSize, $column, $where, $order);

            if (!$list) {
                return [
                    'status' => 1,
                    'data' => [],
                ];
            }
            $groupList = Group::getNameList();
            if ($list['items']) {
                foreach ($list['items'] as $index => $v) {
                    $list['items'][$index]['group_name'] = null;
                    if (isset($groupList[$v['group_id']])) {
                        $list['items'][$index]['group_name'] = $groupList[$v['group_id']]['name'];
                    }
                    unset($list['items'][$index]['php_password']);
                }
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

    // 更新审核状态
    public static function updateStatus($post, $ext)
    {
        try {
            $realModel = new \GameUserRealModel();
            $info = $realModel->getInfoById($post['id'], ['id', 'user_id', 'real_name', 'status']);
            if (!$info) {
                throw new \Exception('信息不存在');
            }
            $update = [];
            $update['status'] = $post['status'];
            isset($post['real_name']) && $update['plan_name'] = $post['real_name'];
            isset($post['gender']) && $update['gender'] = $post['gender'];
            isset($post['id_card_number']) && $update['id_card_number'] = $post['id_card_number'];
            isset($post['content']) && $update['content'] = $post['content'];
            isset($post['audit_time']) && $update['audit_time'] = $post['audit_time'];
            $realModel->updateData($update, $post['id']);
            if ($realModel->getErrors()) {
                throw new \Exception('更新信息失败');
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
