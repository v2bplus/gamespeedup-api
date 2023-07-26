<?php

namespace Services\Game;

class Region extends \Service
{
    // 增加区域
    public static function addRegion($post)
    {
        try {
            $regionModel = new \GameRegionModel();
            $regionInfo = [];
            $regionInfo['name'] = $post['name'];
            $regionInfo['sort'] = $post['sort'];
            $regionInfo['remark'] = $post['remark']??null;

            $id = $regionModel->addData($regionInfo);
            if ($regionModel->getErrors()) {
                throw new \Exception('添加区域失败');
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

    public static function editRegion($post)
    {
        try {
            $regionModel = new \GameRegionModel();
            $info = $regionModel->getInfoById($post['id'], ['id', 'name']);
            if (!$info) {
                throw new \Exception('信息不存在');
            }

            $data = [];
            $data['id'] = $post['id'];
            $data['name'] = $post['name'];
            $data['sort'] = $post['sort'];
            $data['remark'] = $post['remark']??null;

            $regionModel->updateData($data, $post['id']);
            if ($regionModel->getErrors()) {
                throw new \Exception('更新区域信息失败');
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

    public static function getAll($page, $pageSize, $order)
    {
        try {
            $where = [];

            $groupModel = new \GameRegionModel();
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
}
