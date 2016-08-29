<?php

namespace Home\Controller;

use Think\Controller;
use Com\DDMCloud\UcClient;

class DouserController extends Controller {

    function __construct() {
        parent::__construct();
        $this->_before();
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

    public function applycash() {
        $mL = D('log');
        $mUser = D('user');
        $mMoneyAccount = D('moneyaccount');
        $mApplyCash = D('applycash');
        $mOrder = D('order');
        $username = I('session.uc_username');
        $moneyAccount = $mMoneyAccount->getMoneyaccountById(I('moneyaccount'));
        $haveMoney = $mUser->getMoney($username);
        $money = I('money');
        if ($moneyAccount['username'] != $username) {
            return;
        }
        if ($haveMoney < $money || $money < 50) {
            $this->error(L('web_lang_applycash_0'));
            return;
        }
        $data = array(
            'moneyaccount' => $moneyAccount['id'],
            'realname' => $moneyAccount['realname'],
            'account' => $moneyAccount['account'],
            'money' => $money,
            'paymoney' => $money,
        );
        $result = $mApplyCash->addApply($username, $data);
        if ($result) {
            $order = $mOrder->addOrder([
                'money' => -$money,
                'paymoney' => -$money,
                'info' => '提现操作',
                'username' => $username,
                'status' => '1'
            ]);
            $result = $mUser->takeMoney($username, $money);
        }
        $mL->logDefault('applycash', "{$username}申请提现{$money}元", $order);
        $this->success(L('web_lang_applycash_1'));
    }

}
