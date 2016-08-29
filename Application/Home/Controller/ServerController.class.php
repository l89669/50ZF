<?php

namespace Home\Controller;

use Think\Controller;
use Com\DDMCloud\UcClient;

class ServerController extends Controller {

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

    public function servers() {
        $username = I('session.uc_username');
        $sm = D('servers');
        $servers = $sm->searchServerByUsername($username);
        $this->assign('servers', $servers);
        $this->display();
    }

    public function setrate() {
        $username = I('session.uc_username');
        $id = I('id');
        $mPW = D('payway');
        $mS = D('servers');
        $pw = $mPW->getServerPayway($id);
        $this->assign('payway', $pw);
        $this->display();
    }

    public function info() {
        $mS = D('servers');
        $username = I('session.uc_username');
        $id = I('id');
        $server = $mS->loadServer($id);
        $this->assign([
            'server' => $server,
        ]);
        $this->display();
    }

    public function infoedit() {
        $mS = D('servers');
        $username = I('session.uc_username');
        $id = I('id');
        $server = $mS->loadServer($id);
        $this->assign([
            'server' => $server,
        ]);
        $this->display();
    }

    public function manual() {
        $this->display();
    }

    public function player() {
        $mS = D('servers');
        $mP = D('player');
        $username = I('session.uc_username');
        $serverId = I('id');
        $servers = $mS->searchServerByUsername($username);
        if (!$serverId) {
            $serverId = $servers[0]['id'];
        }
        $server = $mS->loadServer($serverId);
        $order = I('order', 'id');
        $players = $mP->searchPlayerByServer($server['id'], false, null, null, "`credit`>'0' || `money`>'0'", "{$order} desc");
        //////分页
        $this->assign([
            'players' => $players,
            'server' => $server,
            'servers' => $servers,
        ]);
        $this->display('player_server');
    }

    public function playerlog() {
        // $this->error('功能完善中');
        $mS = D('servers');
        $mP = D('player');
        $mPO = D('playerorder');
        $username = I('session.uc_username');
        $playerName = I('playerName');
        $serverId = I('id');
        $servers = $mS->searchServerByUsername($username);
        if (!$serverId) {
            $serverId = $servers[0]['id'];
        }
        $server = $mS->loadServer($serverId);
        $where['status'] = 1;
        if ($serverId) {
            $where['server'] = $serverId;
        }
        if ($playerName) {
            $where['player'] = $playerName;
        }
        $p = I('p', 1);
        $per = 20;
        $count = $mPO->where($where)->count();
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
        $logs = $mPO->page("{$p},{$per}")->search($where, false, null, null, 'id desc');
        $this->assign([
            'maxpage' => $maxPage,
            'page' => $show,
            'server' => $server,
            'servers' => $servers,
            'logs' => $logs,
        ]);
        $this->display('player_log');
    }

}
