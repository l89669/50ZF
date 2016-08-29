<?php

namespace Home\Controller;

use Think\Controller;
use Com\DDMCloud\DDMPay\DDM;

class DoserverpayController extends Controller {

    function __construct() {
        parent::__construct();
        $this->_before();
    }

    public function _before() {
        $mS = D('servers');
        $sid = I('id');
        if ($sid) {
            $server = $mS->loadServer($sid);
            if (!$server) {
                exit();
            }
        }
    }

    public function create() {
        $mL = D('log');
        $mS = D('servers');
        $mO = D('order');
        $mU = D('user');
        $mPayWay = D('payway');
        $sid = I('id');
        $server = $mS->loadServer($sid);
        $playerName = I('playername');
        $money = I('money');
        $payway = I('payway');
        cookie('playername', $playerName);
        if (!$playerName || !($money > 0) || ($money > 1000000) || !$payway) {
            $this->error('未填写完整或金额不符合要求');
        }
        $tmp = split('_', $payway);
        if (count($tmp) < 3) {//参数错误
            $mL->logDanger('ordercreate', '订单创建失败[支付方式被篡改]', null);
            return;
        }
        $mU->updateGain($server['username']);
        $divide = ($mPayWay->getdivide($server['username'], $tmp[1], $tmp[2])) / 100; //取分成量
        $orderId = $mO->addOrder([//创建系统订单
            'money' => sprintf("%.2f", $money * $divide), //分成信息录入
            'paymoney' => $money,
            'player' => $playerName,
            'username' => $server['username'],
            'server' => $sid,
            'info' => "玩家{$playerName}为{$sid}号服务器充值{$money}元",
        ]);
        $order = $mO->loadOrder($orderId);
        $param = [//支付平台创建订单配置
            'partner' => '1',
            'payway' => $tmp[1],
            'body' => $order['info'],
            'subject' => $order['info'],
            'total_fee' => $money,
            'order' => $orderId,
            'notifyurl' => 'http://' . $_SERVER['HTTP_HOST'] . '/index.php/Callback/notify',
        ];
        if ($tmp[2] != 'all') {
            $param['paytype'] = $tmp[2];
        }
        $ddmPay = new DDM($param);
        $ddmPay->apiId(C('pay_ddm_partner'));
        $ddmPay->apiKey(C('pay_ddm_key'));
        $ddmPayOrder = $ddmPay->create();
        $mO->saveOrder($orderId, [//保存订单信息
            'paydata' => json_encode([
                'ddmorder' => ['order' => $ddmPayOrder['result']['order'], 'data' => $ddmPayOrder],
                'payway' => $tmp[1],
                'paytype' => $tmp[2],
                    ], JSON_UNESCAPED_UNICODE)
        ]);
        if ($ddmPayOrder['error'] > 0) {//订单申请错误
            $this->error("内部错误" . $ddmPayOrder['error']);
        }
        $this->assign([
            'ddmPayOrdderParam' => $param,
            'ddmPayOrder' => $ddmPayOrder,
            'order' => $order,
            'server' => $server,
        ]);
        $mL->logDefault('ordercreate', "订单创建成功[渠道号{$ddmPayOrder['result']['order']}]", $order['order']);
        $this->redirect('doserverpay/query', ['order' => $order['order']]);
    }

    public function query() {
        $mO = D('order');
        $mS = D('servers');
        $order = I('order');
        $order = $mO->loadOrder($order);
        $server = $mS->loadServer($order['server']);
        $paydata = $mO->reviewPaydata(json_decode($order['paydata'], true));
        $ddmPayOrder = $paydata['ddmorder']['data'];
        $this->assign([
            'paydata' => $paydata,
            'ddmPayOrder' => $ddmPayOrder,
            'order' => $order,
            'server' => $server,
        ]);
        $this->display('Serverpay/query');
    }

    public function gopay() {
        $mS = D('servers');
        $mO = D('order');
        $order = I('order');
        $ddmorder = I('ddmorder');
        $type = I('type');
        $cardId = I('cardid');
        $cardPwd = I('cardpwd');
        $payUrl = DDM::goPay($ddmorder, false);
        if ($type != 'card') {
            header("location: {$payUrl}");
            return;
        }
        //卡密充值处理开始
        $result = DDM::goPay_Card($ddmorder, $cardId, $cardPwd);
        if ($result['error'] > 0) {
            $this->error('内部错误');
            return;
        }
        if ($result['result']['error'] == 0) {
            $mO->saveOrder($order, [
                'status' => '2',
            ]);
            $this->success('卡密已经提交,请等待充值结果返回...', U("doserverpay/result?order=$order"));
            return;
        }
        $this->error($result['result']['reason']);
        return;
    }

    public function result() {
        $mS = D('servers');
        $mO = D('order');
        $order = I('order');
        $order = $mO->loadOrder($order);
        $server = $mS->loadServer($order['server']);
        switch ($order['status']) {
            case '0':
                $order['status'] = "<span style='color: red; font-weight: bold;'>{$mO->reviewStatus($order['status'])}</span>";
                break;
            case '1':
                $order['status'] = "<span style='color: blue; font-weight: bold;'>{$mO->reviewStatus($order['status'])}</span>";
                break;
            case '2':
                $order['status'] = "<span style='color: orange; font-weight: bold;'>{$mO->reviewStatus($order['status'])}</span>";
                break;
            default:
                $order['status'] = "<span style='color: red; font-weight: bold;'>{$mO->reviewStatus($order['status'])}</span>";
                break;
        }
        $this->assign([
            'order' => $order,
            'paydata' => $mO->reviewPaydata(json_decode($order['paydata'], true)),
            'server' => $server,
        ]);
        $this->display('Serverpay/result');
    }

}
