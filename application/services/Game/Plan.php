<?php

namespace Services\Game;

class Plan extends \Service
{
    public static function addPlan($post)
    {
        try {

            $planModel = new \GameVipPlanModel();
            $name = $post['plan_name'] ?? null;
            $check = $planModel->checkName($name);
            if ($check) {
                throw new \Exception('已存在名为: '.$name.'的套餐');
            }
            $insert = [];
            $insert['plan_name'] = $name;
            $insert['money'] = $post['money'] ?? 0;
            $insert['day_time'] = $post['day_time'];
            $insert['gift_day_time'] = $post['gift_day_time'] ?? 0;
            $insert['content'] = $post['content'] ?? null;
            $insert['show'] = $post['show'];

            $id = $planModel->addData($insert);
            if ($planModel->getErrors()) {
                throw new \Exception('添加vip套餐失败');
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

    public static function editPlan($post)
    {
        try {
            $planModel = new \GameVipPlanModel();
            $check = $planModel->checkName($post['plan_name'], $post['id']);
            if ($check) {
                throw new \Exception('已存在名字为: '.$post['plan_name'].'的套餐');
            }

            $update = [];
            $update['plan_name'] = $post['plan_name'];
            isset($post['money']) && $update['money'] = $post['money'];
            isset($post['day_time']) && $update['day_time'] = $post['day_time'];
            isset($post['gift_day_time']) && $update['gift_day_time'] = $post['gift_day_time'];
            isset($post['content']) && $update['content'] = $post['content'];
            isset($post['show']) && $update['show'] = $post['show'];
            isset($post['sort']) && $update['sort'] = $post['sort'];

            $planModel->updateData($update, $post['id']);
            if ($planModel->getErrors()) {
                throw new \Exception('编辑vip套餐失败');
            }

            return [
                'status' => 1,
                'data' => [],
                'msg' => '',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public static function getInfo($planId, $uid)
    {
        try {
            $planModel = new \GameVipPlanModel();
            $column = ['id', 'plan_name', 'money', 'day_time', 'gift_day_time', 'content', 'show', 'sort'];
            $data = $planModel->getInfoById($planId, $column);
            if (!$data) {
                throw new \Exception('套餐信息不存在');
            }
            if (!$data['show']) {
                throw new \Exception('此套餐不可售');
            }

            return [
                'status' => 1,
                'data' => $data,
                'msg' => '',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public static function getAllList($page, $pageSize, $sort)
    {
        try {
            $where = [];
            $planModel = new \GameVipPlanModel();
            $list = $planModel->getList($page, $pageSize, null, $where, $sort);

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
