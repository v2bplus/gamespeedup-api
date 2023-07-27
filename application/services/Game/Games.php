<?php

namespace Services\Game;

class Games extends \Service
{
    const TYPES_PC = 1;
    const TYPES_ANDROID = 2;
    const TYPES_IOS = 3;
    const TYPES_PS = 4;

    public static $types = [
        self::TYPES_PC,self::TYPES_ANDROID,self::TYPES_IOS,self::TYPES_PS
    ];
    // 增加游戏
    public static function addGame($post)
    {
        try {
            $gameModel = new \GameGameModel();
            $name = $post['name'];
            $type = $post['type'];
            $check = $gameModel->checkName($name,$type);
            if ($check) {
                throw new \Exception('已存在名为: '.$name.'的游戏平台');
            }
            $gameInfo = [];
            $gameInfo['name'] = $name;
            $gameInfo['alias'] = $post['alias'];
            $gameInfo['type'] = $post['type'];
            $gameInfo['logo_url'] = $post['logo_url'];
            $gameInfo['cover_img_url'] = $post['cover_img_url'];
            $gameInfo['region_ids'] = $post['region_ids']??[];
            $gameInfo['status'] = $post['status'];

            $id = $gameModel->addData($gameInfo);
            if ($gameModel->getErrors()) {
                throw new \Exception('添加游戏失败');
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

    public static function editGame($post)
    {
        try {
            $gameModel = new \GameGameModel();
            $info = $gameModel->getInfoById($post['id'], ['id', 'name']);
            if (!$info) {
                throw new \Exception('信息不存在');
            }
            $check = $gameModel->checkName($post['name'],$post['type'], $post['id']);
            if ($check) {
                throw new \Exception('已存在名字为: '.$post['name']);
            }

            $data = [];
            $data['id'] = $post['id'];
            $data['alias'] = $post['alias'];
            $data['type'] = $post['type'];
            $data['logo_url'] = $post['logo_url'];
            $data['cover_img_url'] = $post['cover_img_url'];
            $data['region_ids'] = $post['region_ids'];
            $data['status'] = $post['status'];

            $gameModel->updateData($data, $post['id']);
            if ($gameModel->getErrors()) {
                throw new \Exception('更新游戏信息失败');
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

    public static function getAllList($page, $pageSize, $order)
    {
        try {
            $where = [];

            $groupModel = new \GameGameModel();
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
