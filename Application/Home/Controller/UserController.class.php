<?php

namespace Home\Controller;

use Think\Controller;
use Com\DDMCloud\UcClient;

class UserController extends Controller {

    function __construct() {
        parent::__construct();
        $allow = $this->_before();
        if (!$allow) {
            $this->error(L('web_lang_2'), U('index/index'));
            exit();
        }
    }

    public function _before() {
        session_start();
        UcClient::init();
        $action = ACTION_NAME;
        $allow = array('login', 'forgetpwd', 'logout');
        if (!in_array($action, $allow) && !session('uc_login')) {
            return false;
        }
        return true;
    }

    public function login() {
        $mL = D('log');
        $username = I('username');
        $password = I('password');
        list($uc_uid, $uc_username,, $uc_email) = uc_user_login($username, $password);
        if ($uc_uid > 0) {//登录成功
            $mL->logDefault('login', "[{$username}]登录成功", null);
            session('uc_login', true);
            session('uc_uid', $uc_uid);
            session('uc_username', $uc_username);
            session('uc_email', $uc_email);
            echo uc_user_synlogin($uc_uid);
            $string = L('web_lang_login_0');
        } else {
            $mL->logDefault('login', "[{$username}]登录失败[{$uc_uid}]", null);
            $error = true;
            $string = L("web_lang_login_$uc_uid");
            if ($string == "web_lang_login_$uc_uid") {
                $string = L('web_lang_login_unknown');
            }
        }
        switch ($error) {
            case true://失败
                $this->error($string);
                break;
            case false://成功
                //$this->success($string, U('user/main'));
                $this->redirect('user/main');
                break;
        }
    }

    public function forgetpwd() {
        
    }

    public function main() {
        $mO = D('order');
        $mU = D('user');
        $username = I('session.uc_username');
        $user = $mU->loadUser($username);
        $order = $mO->searchOrderByUsername($username, true, 0, 5, [
            'status' => '1', 'paymoney' => ['gt', '0']
        ]);
        $this->assign(['order' => $order, 'user' => $user]);
        $this->display();
    }

    public function logout() {
        session('uc_login', false);
        echo uc_user_synlogout();
        //$this->success(L('web_lang_logout_0'), U('index/index'));
        $this->redirect('index/index');
    }

    public function editprofile() {
        $mL = D('log');
        UcClient::init();
        $m = D('userinfo');
        $username = I('session.uc_username');
        list($uid, $username, $email) = uc_get_user($username);
        session('uc_email', $email);
        if (I('submit')) {
            $info['qq'] = I('qq');
            $info['phone'] = I('phone');
            $m->saveUser($username, $info);
            $mL->logDefault('userinfo', "{$username}编辑了个人信息", null);
        }
        $this->assign('userinfo', $m->loadUser($username));
        $this->display();
    }

    public function moneyaccount() {
        $username = I('session.uc_username');
        $submit = I('get.submit');
        $realname = I('realname');
        $account = I('account');
        $type = I('type');
        $id = I('id');
        $m = D('moneyaccount');
        $mt = D('moneyaccounttype');
        $moneyaccount = $m->getMoneyaccount($username);
        $moneyaccounttype = $mt->getMoneyAccountTypeList();
        if ($id) {//权限判断
            $a = $m->searchMoneyaccountById($id);
            if ($a['username'] != $username) {
                $this->error('请刷新后在试');
            }
        }
        $this->assign('moneyaccount', $moneyaccount);
        $this->assign('moneyaccounttype', $moneyaccounttype);
        $this->assign('account', $a);
        switch ($submit) {
            case 'edit':
                if ($realname && $type && $account) {
                    $m->editMoneyaccount($id, array('realname' => $realname, 'account' => $account, 'type' => $type));
                    break;
                }
                $this->display('moneyaccount_edit');
                exit;
                break;
            case 'del':
                $m->delMoneyaccount($username, $a['type'], $a['account']);
                break;
            case 'add':
                if (!$realname || !$account || !$type) {
                    break;
                }
                $m->addMoneyaccount($username, $type, $realname, $account);
                break;
        }
        if (I('post.from') || I('get.from')) {
            $this->success('ok', U('user/moneyaccount?submit=submit'));
            return;
        }
        $this->display();
    }

    public function record() {
        $mO = D('order');
        $p = I('p', 1);
        $username = I('session.uc_username');
        $per = 20;
        $count = $mO->where(['username' => $username, 'status' => '1'])->count();
        $maxPage = ceil($count / $per);
        if ($p > $maxPage) {
            $p = $maxPage;
        }if ($p < 0) {
            $p = 1;
        }
        $_GET['p'] = $p;
        $_POST['p'] = $p;
        $page = new \Think\Page($count, $per); // 实例化分页类 传入总记录数和每页显示的记录数
        $page->setConfig('theme', '<ul class="pagination"><li><span class="rows">共' . $maxPage . '页记录</span></li><li>%FIRST%</li><li>%UP_PAGE%</li><li class="active"><span class="current">' . $p . '</span></li><li>%DOWN_PAGE%</li><li>%END%</li></ul>');
        $show = $page->show(); // 分页显示输出
        $record = $mO->where([
                    'username' => $username,
                    'status' => '1',
                ])->order('id desc')->page("{$p},{$per}")->select();
        $this->assign([
            'maxpage' => $maxPage,
            'page' => $show,
            'record' => $record,
        ]); // 赋值分页输出
        $this->display(); // 输出模板
    }

    public function applycash() {
        $username = I('session.uc_username');
        $m = D('moneyaccount');
        $moneyaccount = $m->getMoneyaccount($username);
        $this->assign(array('moneyaccount' => $moneyaccount));
        $this->display();
    }

    public function applycashrecord() {
        $mApplyCash = D('applycash');
        $p = I('p', 1);
        $username = I('session.uc_username');
        $per = 20;
        $count = $mApplyCash->where(['username' => $username])->count();
        $maxPage = ceil($count / $per);
        if ($p > $maxPage) {
            $p = $maxPage;
        }if ($p <= 0) {
            $p = 1;
        }
        $_GET['p'] = $p;
        $_POST['p'] = $p;
        $page = new \Think\Page($count, $per); // 实例化分页类 传入总记录数和每页显示的记录数
        $page->setConfig('theme', '<ul class="pagination"><li><span class="rows">共' . $maxPage . '页记录</span></li><li>%FIRST%</li><li>%UP_PAGE%</li><li class="active"><span class="current">' . $p . '</span></li><li>%DOWN_PAGE%</li><li>%END%</li></ul>');
        $show = $page->show(); // 分页显示输出
        $record = $mApplyCash->getApplyByUsername($username, true, ($p - 1) * $per, $p * $per);
        $this->assign([
            'maxpage' => $maxPage,
            'page' => $show,
            'record' => $record,
        ]); // 赋值分页输出
        $this->display(); // 输出模板
    }

    public function recordinfo() {
        $mO = D('order');
        $mS = D('servers');
        $username = I('session.uc_username');
        $order = I('order');
        $order = $mO->loadOrder($order);
        $server = $mS->loadServer($order['server']);
        if ($order['username'] != $username) {
            return;
        }
        $this->assign([
            'order' => $order,
            'server' => $server,
            'paydata' => $mO->reviewPaydata(json_decode($order['paydata'], true)),
        ]);
        $this->display();
    }

    public function gain2money() {
        $mU = D('user');
        $username = I('session.uc_username');
        $money = $mU->gainToMoney($username);
        $this->success("转入成功,当前余额: <strong>{$money}</strong>", null, 1);
    }

}
