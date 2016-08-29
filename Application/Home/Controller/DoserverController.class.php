<?php

namespace Home\Controller;

use Think\Controller;
use Com\DDMCloud\UcClient;

class DoServerController extends Controller {

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
        $username = I('session.uc_username');
        $ms = D('servers');
        $id = I('id');
        $s = $ms->loadServer($id);
        if ($id && $s['username'] != $username) {
            return false;
        }
        return true;
    }

    public function applyserver() {
        $mL = D('log');
        $username = I('session.uc_username');
        $ms = D('servers');
        $sname = I('sname');
        $shost = I('shost');
        $stext = I('stext');
        $data['name'] = $sname;
        $data['ip'] = $shost;
        $data['info'] = $stext;
        $data['username'] = $username;
        $data['key'] = $this->getRandChar(16);
        $result = $ms->addServer($data);
        $mL->logDefault('applyserver', "{$username}申请服务器[{$result}({$sname})]", $result);
        if ($result) {
            $this->success(L('web_lang_3'));
        } else {
            $this->error(L('web_lang_4'));
        }
    }

    public function randomserverkey() {
        $mL = D('log');
        $username = I('session.uc_username');
        $ms = D('servers');
        $id = I('id');
        $result = $ms->saveServer($id, array('key' => $this->getRandChar(16)));
        $mL->logDefault('resetserverkey', "{$username}重置服务器密钥[{$id}]", $id);
        if ($result) {
            $this->success(L('web_lang_3'));
        } else {
            $this->error(L('web_lang_4'));
        }
    }

    private function getRandChar($length) {
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;

        for ($i = 0; $i < $length; $i++) {
            $str.=$strPol[rand(0, $max)]; //rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }

        return $str;
    }

    public function setrate() {
        $mL = D('log');
        $username = I('uc_username');
        $id = I('id');
        $ms = D('servers');
        $rate = json_encode(I('param.'), JSON_UNESCAPED_UNICODE);
        $result = $ms->saveServer($id, ['rate' => $rate]);
        $mL->logDefault('resetserverkey', "{$username}设置[{$id}]号服务器充值比例{$rate}", $id);
        if ($result) {
            $this->success(L('web_lang_3'));
        } else {
            $this->error(L('web_lang_4'));
        }
    }

    public function infoedit() {
        $mL = D('log');
        $mS = D('servers');
        $username = I('session.uc_username');
        $id = I('id');
        $result = $mS->saveServer($id, [
            'name' => I('name'),
            'ip' => I('ip'),
            'logourl' => I('logourl'),
            'wwwurl' => I('wwwurl'),
            'url' => I('url'),
            'info' => I('info'),
        ]);
        $mL->logDefault('editserverinfo', "{$username}编辑[{$id}]号服务器信息", $id);
        if ($result) {
            $this->success(L('web_lang_3'));
        } else {
            $this->error(L('web_lang_4'));
        }
    }

    public function manual() {
        $username = I('session.uc_username');
        $mL = D('log');
        $mO = D('order');
        $mP = D('player');
        $mPO = D('playerorder');
        $mPL = D('playerlog');
        $username = I('session.uc_username');
        $id = I('id');
        $player = I('player');
        $credit = I('credit');
        $reason = I('reason');
        $type = I('type');
        $s_player = $mP->loadPlayer($id, $player);
        if ($type == "2") {
            $credit = -$credit;
        }
        $order = $mPO->addPlayerOrder([
            'server' => $id,
            'player' => $player,
            'info' => "手动操作[{$credit}]点券,理由[{$reason}]",
            'username' => $username,
            'credit' => $credit,
        ]);
        $result = $mPO->payPlayerOrder($order);
        //$result = $mP->addCredit($id, $player, $credit); //发放点券
        //$mPL->log("U_{$order}", $id, $player, $s_player['credit'], $s_player['credit'] + $credit);
        $mL->logDefault('manual', "{$username}为[{$id}]服玩家[{$player}]操作[{$credit}]点券,理由[{$reason}]", $order);
        if ($result) {
            $this->success(L('web_lang_3'));
        } else {
            $this->error(L('web_lang_4'));
        }
    }

}
