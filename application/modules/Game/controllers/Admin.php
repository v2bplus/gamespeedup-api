<?php

// admin模块
use Services\Game\Login;
use Services\Game\AdminUser;
use Services\Game\Group;
use Services\Game\Plan;
use Services\Game\User;

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
            'sort' => $sort
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
        $name = $this->getPost('name', null);
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

    public function user_addAction()
    {
        $password = $this->getPost('password', null);
        $expire = $this->getPost('expire_date', null);
        $mobile = $this->getPost('mobile', null);
        $post = [
            'email' => $email,
            'password' => $password,
            'expire' => $expire,
            'mobile' => $mobile,
        ];
        $rules = [
            'mobile' => [
                ['optional'],
                ['Length', 11, 'message' => '手机号格式不正确'],
            ],
            'password' => [
                ['required', 'message' => 'password不能为空'],
            ],
            'expire' => [
                ['optional'],
                ['date', 'message' => '日期格式不正确'],
            ],
        ];
        $rs = Validator::customerValidate($post, $rules);
        if (!$rs->validate()) {
            Response::renderJson(GAME_ADMIN_STATUS_ERROR, '验证错误', $rs->errors());
        }
        $detail = AdminUser::addUser($post, $this->adminId);
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
}
