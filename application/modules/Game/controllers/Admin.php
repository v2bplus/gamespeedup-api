<?php

// admin模块
use Services\Game\AdminUser;
use Services\Game\Common;
use Services\Game\Games;
use Services\Game\Group;
use Services\Game\Login;
use Services\Game\Plan;
use Services\Game\Region;
use Services\Game\User;
use Services\Game\UserReal;
use Services\Game\UserVip;

class AdminController extends \CoreController\GameAdminAbstract
{
    public $_white_actions = [
        'index', 'ping', 'login',
    ];

    public function init()
    {
        $action = $this->getRequest()->getActionName();
        if (ENVIRON == 'dev') {
            $this->needLogin = false;
        } else {
            if (in_array($action, $this->_white_actions)) {
                $this->needLogin = false;
            }
        }

        parent::init();
    }

    public function indexAction()
    {
        Response::appJson(GAME_ADMIN_STATUS_SUCCESS, $this->moduleName.': Admin首页', []);
    }

    public function myinfoAction()
    {
        $detail = AdminUser::getUserInfo($this->adminId);
        if (1 !== $detail['status']) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, $detail['msg']);
        }
        Response::renderJson(GAME_ADMIN_STATUS_SUCCESS, '处理成功', $detail['data']);
    }

    // 管理员登陆
    public function loginAction()
    {
        $username = $this->getPost('username', null, true);
        $password = $this->getPost('password', null);
        $post = [
            'username' => $username,
            'pwd' => $password,
        ];
        $rules = [
            'username' => [
                ['required', 'message' => '用户名不能为空'],
            ],
            'pwd' => [
                ['required', 'message' => '密码不能为空'],
                ['lengthMin', 6, 'message' => '密码格式不正确'],
            ],
        ];
        $rs = Validator::customerValidate($post, $rules);
        if (!$rs->validate()) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, '验证错误', $rs->errors());
        }
        $rs = Login::adminLogin($username, $password);
        if (1 !== $rs['status']) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, $rs['msg']);
        }
        Response::renderJson(GAME_ADMIN_STATUS_SUCCESS, '登陆成功', $rs['data']);
    }

    public function logoutAction()
    {
        Login::logout();
        Response::renderJson(GAME_ADMIN_STATUS_SUCCESS, '操作成功');
    }

    public function adminuser_listAction()
    {
        $detail = AdminUser::getAll($this->page, $this->pageSize, $this->sortInfo, $this->user);
        if (1 !== $detail['status']) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, $detail['msg']);
        }
        Response::renderJson(GAME_ADMIN_STATUS_SUCCESS, '处理成功', $detail['data']);
    }

    public function group_listAction()
    {
        $info = Group::getAllList($this->page, $this->pageSize, $this->sortInfo);
        if (1 !== $info['status']) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, $info['msg']);
        }
        Response::renderJson(GAME_ADMIN_STATUS_SUCCESS, '处理成功', $info['data']);
    }

    public function group_addAction()
    {
        $name = $this->getPost('name', null);
        $attribute = $this->getPost('attribute', null);
        $remark = $this->getPost('remark', null);
        $post = [
            'name' => $name,
            'remark' => $remark,
            'attribute' => $attribute,
        ];
        $rules = [
            'name' => [
                ['required', 'message' => '名字不能为空'],
            ],
            'attribute' => [
                ['required', 'message' => '属性不能为空'],
            ],
            'remark' => [
                ['optional'],
                ['lengthMax', 255, 'message' => '格式不正确'],
            ],
        ];
        $rs = Validator::customerValidate($post, $rules);
        if (!$rs->validate()) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, '验证错误', $rs->errors());
        }
        $detail = Group::addGroup($post);
        if (1 !== $detail['status']) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, $detail['msg']);
        }
        Response::renderJson(GAME_ADMIN_STATUS_SUCCESS, '处理成功', $detail['data']);
    }

    public function group_updateAction()
    {
        $id = $this->getPost('id', 0);
        $name = $this->getPost('name', null);
        $attribute = $this->getPost('attribute', null);
        $remark = $this->getPost('remark', null);
        $post = [
            'id' => $id,
            'name' => $name,
            'attribute' => $attribute,
            'remark' => $remark,
        ];
        $rules = [
            'id' => [
                ['required', 'message' => 'ID不能为空'],
            ],
            'name' => [
                ['required', 'message' => '名字不能为空'],
            ],
            'attribute' => [
                ['required', 'message' => '属性不能为空'],
            ],
            'remark' => [
                ['optional'],
                ['lengthMax', 255, 'message' => '格式不正确'],
            ],
        ];
        $rs = Validator::customerValidate($post, $rules);
        if (!$rs->validate()) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, '验证错误', $rs->errors());
        }
        $detail = Group::editGroup($post);
        if (1 !== $detail['status']) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, $detail['msg']);
        }
        Response::renderJson(GAME_ADMIN_STATUS_SUCCESS, '处理成功', $detail['data']);
    }

    public function plan_listAction()
    {
        $detail = Plan::getAllList($this->page, $this->pageSize, $this->sortInfo);

        if (1 !== $detail['status']) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, $detail['msg']);
        }
        Response::renderJson(GAME_ADMIN_STATUS_SUCCESS, '处理成功', $detail['data']);
    }

    public function plan_addAction()
    {
        $name = $this->getPost('plan_name', null);
        $money = $this->getPost('money', 0);
        $day_time = $this->getPost('day_time', 0);
        $gift_day_time = $this->getPost('gift_day_time', 0);
        $content = $this->getPost('content', null);
        $show = $this->getPost('show', null);
        $sort = $this->getPost('sort', 0);
        $post = [
            'plan_name' => $name,
            'money' => $money,
            'day_time' => $day_time,
            'gift_day_time' => $gift_day_time,
            'content' => $content,
            'show' => $show,
            'sort' => $sort,
        ];
        $rules = [
            'plan_name' => [
                ['required', 'message' => '名字不能为空'],
            ],
            'money' => [
                ['integer', 'message' => '金额格式不正确'],
            ],
            'day_time' => [
                ['integer', 'message' => '时长(天)格式不正确'],
            ],
            'gift_day_time' => [
                ['integer', 'message' => '赠送时长(天)格式不正确'],
            ],
            'content' => [
                ['required', 'message' => '套餐内容不能为空'],
            ],
            'show' => [
                ['required', 'message' => '显示不能为空'],
                ['in', [0, 1], 'message' => '显示状态不正确'],
            ],
            'sort' => [
                ['integer', 'message' => '排序格式不正确'],
            ],
        ];
        $rs = Validator::customerValidate($post, $rules);
        if (!$rs->validate()) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, '验证错误', $rs->errors());
        }
        $detail = Plan::addPlan($post);
        if (1 !== $detail['status']) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, $detail['msg']);
        }
        Response::renderJson(GAME_ADMIN_STATUS_SUCCESS, '处理成功', $detail['data']);
    }

    public function plan_updateAction()
    {
        $id = $this->getPost('id', 0);
        $name = $this->getPost('plan_name', null);
        $money = $this->getPost('money', 0);
        $day_time = $this->getPost('day_time', 0);
        $gift_day_time = $this->getPost('gift_day_time', 0);
        $sort = $this->getPost('sort', null);
        $show = $this->getPost('show', null);
        $content = $this->getPost('content', null);

        $post = [
            'id' => $id,
        ];
        isset($name) && $post['plan_name'] = $name;
        isset($sort) && $post['sort'] = $sort;
        isset($money) && $post['sort'] = $sort;
        isset($day_time) && $post['day_time'] = $day_time;
        isset($gift_day_time) && $post['gift_day_time'] = $gift_day_time;
        isset($show) && $post['show'] = $show;
        $content && $post['content'] = $content;

        $rules = [
            'id' => [
                ['required', 'message' => 'ID不能为空'],
            ],
            'name' => [
                ['optional'],
                ['required', 'message' => '名字内容不能为空'],
            ],
            'sort' => [
                ['optional'],
                ['integer', 'message' => '排序格式不正确'],
            ],
            'show' => [
                ['optional'],
                ['required', 'message' => '显示不能为空'],
                ['in', [0, 1], 'message' => '显示状态不正确'],
            ],
            'content' => [
                ['optional'],
                ['required', 'message' => '订阅内容不能为空'],
            ],
        ];

        $rs = Validator::customerValidate($post, $rules);
        if (!$rs->validate()) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, '验证错误', $rs->errors());
        }

        $detail = Plan::editPlan($post);
        if (1 !== $detail['status']) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, $detail['msg']);
        }
        Response::renderJson(GAME_ADMIN_STATUS_SUCCESS, '处理成功', $detail['data']);
    }

    public function user_listAction()
    {
        $detail = User::getAll($this->page, $this->pageSize, $this->sortInfo);
        if (1 !== $detail['status']) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, $detail['msg']);
        }
        Response::renderJson(GAME_ADMIN_STATUS_SUCCESS, '处理成功', $detail['data']);
    }

    public function user_updateAction()
    {
        $id = $this->getPost('id', 0);
        $password = $this->getPost('password', null);
        $mobile = $this->getPost('mobile', null);
        $remark = $this->getPost('remark', null);
        $post = [
            'id' => $id,
            'password' => $password,
            'mobile' => $mobile,
            'remark' => $remark,
        ];
        $rules = [
            'id' => [
                ['required', 'message' => 'ID不能为空'],
            ],
            'email' => [
                ['required', 'message' => 'email不能为空'],
                ['email', 'message' => '不是正确的邮箱地址'],
            ],
            'password' => [
                ['required', 'message' => 'password不能为空'],
            ],
            'mobile' => [
                ['optional'],
                ['Length', 11, 'message' => '手机号格式不正确'],
            ],
            'remark' => [
                ['optional'],
                ['lengthMax', 255, 'message' => '格式不正确'],
            ],
        ];
        $rs = Validator::customerValidate($post, $rules);
        if (!$rs->validate()) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, '验证错误', $rs->errors());
        }
        $detail = User::editInfo($post, $this->adminId);
        if (1 !== $detail['status']) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, $detail['msg']);
        }
        Response::renderJson(GAME_ADMIN_STATUS_SUCCESS, '处理成功', $detail['data']);
    }

    public function real_listAction()
    {
        $detail = UserReal::getAll($this->page, $this->pageSize, $this->sortInfo);
        if (1 !== $detail['status']) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, $detail['msg']);
        }
        Response::renderJson(GAME_ADMIN_STATUS_SUCCESS, '处理成功', $detail['data']);
    }

    public function vip_listAction()
    {
        $detail = UserVip::getAll($this->page, $this->pageSize, $this->sortInfo);
        if (1 !== $detail['status']) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, $detail['msg']);
        }
        Response::renderJson(GAME_ADMIN_STATUS_SUCCESS, '处理成功', $detail['data']);
    }

    public function region_listAction()
    {
        $detail = Region::getAll($this->page, $this->pageSize, $this->sortInfo);
        if (1 !== $detail['status']) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, $detail['msg']);
        }
        Response::renderJson(GAME_ADMIN_STATUS_SUCCESS, '处理成功', $detail['data']);
    }

    public function region_addAction()
    {
        $name = $this->getPost('name', null);
        $sort = $this->getPost('sort', 0);
        $remark = $this->getPost('remark', null);
        $post = [
            'name' => $name,
            'sort' => $sort,
            'remark' => $remark,
        ];
        $rules = [
            'name' => [
                ['required', 'message' => '名字不能为空'],
            ],
            'sort' => [
                ['integer', 'message' => '排序格式不正确'],
            ],
            'remark' => [
                ['optional'],
                ['lengthMax', 255, 'message' => '格式不正确'],
            ],
        ];
        $rs = Validator::customerValidate($post, $rules);
        if (!$rs->validate()) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, '验证错误', $rs->errors());
        }
        $detail = Region::addRegion($post);
        if (1 !== $detail['status']) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, $detail['msg']);
        }
        Response::renderJson(GAME_ADMIN_STATUS_SUCCESS, '处理成功', $detail['data']);
    }

    public function tree_listAction()
    {
        $type = $this->getPost('type', null);
        $post = [
            'type' => $type,
        ];
        $rules = [
            'type' => [
                ['required', 'message' => '数据类型不能为空'],
                ['in', Common::$treeType, 'message' => '数据类型不正确'],
            ],
        ];
        $rs = Validator::customerValidate($post, $rules);
        if (!$rs->validate()) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, '验证错误', $rs->errors());
        }
        $detail = Common::treeList($type);
        if (1 !== $detail['status']) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, $detail['msg']);
        }
        Response::renderJson(GAME_ADMIN_STATUS_SUCCESS, '处理成功', $detail['data']);
    }

    public function game_listAction()
    {
        $detail = Games::getAllList($this->page, $this->pageSize, $this->sortInfo);
        if (1 !== $detail['status']) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, $detail['msg']);
        }
        Response::renderJson(GAME_ADMIN_STATUS_SUCCESS, '处理成功', $detail['data']);
    }

    public function game_addAction()
    {
        $name = $this->getPost('name', null);
        $alias = $this->getPost('alias', null);
        $type = $this->getPost('type', null);
        $logo_url = $this->getPost('logo_url', null);
        $cover_img_url = $this->getPost('cover_img_url', null);
        $region_ids = $this->getPost('region_ids', null);
        $status = $this->getPost('status', 0);
        $remark = $this->getPost('remark', null);
        $post = [
            'name' => $name,
            'alias' => $alias,
            'type' => $type,
            'logo_url' => $logo_url,
            'cover_img_url' => $cover_img_url,
            'region_ids' => $region_ids,
            'status' => $status,
            'remark' => $remark,
        ];
        $rules = [
            'name' => [
                ['required', 'message' => '游戏名字不能为空'],
            ],
            'alias' => [
                ['required', 'message' => '游戏别名不能为空'],
            ],
            'type' => [
                ['required', 'message' => '游戏类型不能为空'],
                ['in', Games::$types, 'message' => '游戏类型不正确'],
            ],
            'logo_url' => [
                ['required', 'message' => 'logo图片地址不能为空'],
            ],
            'cover_img_url' => [
                ['required', 'message' => '封面图片地址不能为空'],
            ],
            'region_ids' => [
                ['optional'],
                ['array', 'message' => '区域格式不正确'],
            ],
            'status' => [
                ['in', [0, 1], 'message' => '状态不正确'],
            ],
            'remark' => [
                ['optional'],
                ['lengthMax', 255, 'message' => '格式不正确'],
            ],
        ];
        $rs = Validator::customerValidate($post, $rules);
        if (!$rs->validate()) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, '验证错误', $rs->errors());
        }
        $detail = Games::addGame($post);
        if (1 !== $detail['status']) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, $detail['msg']);
        }
        Response::renderJson(GAME_ADMIN_STATUS_SUCCESS, '处理成功', $detail['data']);
    }

    public function vip_updateAction()
    {
        $id = $this->getPost('id', 0);
        $status = $this->getPost('status', 0);
        $startTime = $this->getPost('start_time', 0);
        $endTime = $this->getPost('end_time', 0);
        $post = [
            'id' => $id,
            'status' => $status,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ];
        $rules = [
            'id' => [
                ['required', 'message' => 'ID不能为空'],
            ],
            'status' => [
                ['in', UserVip::$status, 'message' => '状态类型不正确'],
            ],
            'start_time' => [
                ['optional'],
                ['integer', 'message' => '开始时间格式不正确'],
            ],
            'end_time' => [
                ['optional'],
                ['integer', 'message' => '结束时间格式不正确'],
            ],
        ];
        $rs = Validator::customerValidate($post, $rules);
        if (!$rs->validate()) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, '验证错误', $rs->errors());
        }
        $detail = UserVip::updateStatus($post, $this->adminId);
        if (1 !== $detail['status']) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, $detail['msg']);
        }
        Response::renderJson(GAME_ADMIN_STATUS_SUCCESS, '处理成功', $detail['data']);
    }

    public function real_updateAction()
    {
        $id = $this->getPost('id', 0);
        $status = $this->getPost('status', 0);
        $remark = $this->getPost('remark', null);
        $post = [
            'id' => $id,
            'status' => $status,
            'remark' => $remark,
        ];
        $rules = [
            'id' => [
                ['required', 'message' => 'ID不能为空'],
            ],
            'status' => [
                ['in', UserReal::$status, 'message' => '状态不正确'],
            ],
            'remark' => [
                ['optional'],
                ['lengthMax', 255, 'message' => '格式不正确'],
            ],
        ];
        $rs = Validator::customerValidate($post, $rules);
        if (!$rs->validate()) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, '验证错误', $rs->errors());
        }
        $detail = UserReal::updateStatus($post, $this->adminId);
        if (1 !== $detail['status']) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, $detail['msg']);
        }
        Response::renderJson(GAME_ADMIN_STATUS_SUCCESS, '处理成功', $detail['data']);
    }
}
