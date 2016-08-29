<?php

namespace Home\Controller;

use Think\Controller;
use Com\DDMCloud\DDMPay\DDM;

class CallbackController extends Controller {

    public function notify() {
        $mL = D('log');
        $mPL = D('playerlog');
        $mS = D('servers');
        $mO = D('order');
        $mU = D('user');
        $mP = D('player');
        $order_no = I('order_no');
        $pay_way = I('pay_way');
        $pay_type = I('pay_type');
        if ($pay_type == '' || !$pay_type || $pay_way == "bank") {
            $pay_type = 'all';
        }
        $pay_amount = I('pay_amount');
        $apiKey = C('pay_ddm_key');
        $result = DDM::notify($apiKey);

        if ($result) {//开始处理业务逻辑
            //
            $s_Order = $mO->loadOrder($order_no);
            $s_Server = $mS->loadServer($s_Order['server']);
            $s_Player = $mP->loadPlayer($s_Order['server'], $s_Order['player']);
            $s_Rate = json_decode($s_Server['rate'], JSON_UNESCAPED_UNICODE);
            //
            $rate = $s_Rate["{$pay_way}_{$pay_type}"];
            if (is_null($rate)) {
                $rate = 100;
            }
            $credit = $s_Order['paymoney'] * $rate;
            if ($s_Order['status'] == '1') {//已经成功
                echo 'success';
                return;
            }
            if ($pay_amount < $s_Order['paymoney']) {//金额校对错误
                return;
            }
            $mU->updateGain($s_Order['username']); //更新分成T+1
            $mU->addGain_Last($s_Order['username'], $s_Order['money']); //发放分成后的资金
            $mP->addMoney($s_Order['server'], $s_Order['player'], $s_Order['paymoney']); //增加充值
            $mP->addCredit($s_Order['server'], $s_Order['player'], $credit); //发放点券
            $mO->saveOrder($order_no, ['status' => '1', 'paytime' => time(),]); //保存订单
            $mS->saveServer($s_Server['id'], ['money' => $s_Server['money'] + $s_Order['money'],]); //设置服务器收益
            $mL->logDefault('callback', "订单[{$order_no}]支付成功", $order_no);
            $mPL->log("U_{$s_Order['order']}", $s_Order['server'], $s_Order['player'], $s_Player['credit'], $s_Player['credit'] + $credit);
            echo 'success'; //如果没问题只能打印出success 打印出其他内容接口会抓取失败
        } else {
            $mL->logDefault('callback', '订单支付结果校验错误', $order_no);
        }
    }

}
