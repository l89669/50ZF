<?php

namespace Home\Controller;

use Think\Controller;
use Com\DDMCloud\DDMPay\DDM;
use Com\DDMCloud\HTTP;

class gopayController extends Controller {

    public function test() {
        echo HTTP::http_getFile("http://www.pulupulu.com/pay/netbankr", [
            'v_oid' => 'MBc9f1190342af83a', //订单号？
            'v_pstatus' => '20', //状态？
            'v_pstring' => '0', //不晓得
            'v_amount' => '648', //金额
            'v_moneytype' => 'CNY', //类型
            'remark2' => '',
            'v_md5str' => 'plplpay', //校验
                ], null, "POST", false);
    }

    public function go() {
       
        $mPlpl = D('plpl');
        $amount = I('amount'); // 订单总金额, 人民币单位：分（如订单总金额为 1 元，此处请填 100）
        $order_no = I('order_no'); // 推荐使用 8-20 位，要求数字或字母，不允许其他字符
        $channel = I('channel', 'wx_pub_qr');
        $client = I('client');
        // $sign = I('sign');
        //$sign = MD5($tradeNo . ($fee * 100) . "123456789aaa");
        if ($sign != MD5($order_no . $amount . "123456789aaa")) {
            //return;
        }
        $client = false;
        switch ($channel) {
            case 'alipay_wap':
                $client = false;
                $payType = "alipay";
                break;
            case 'wx_pub':
                $client = true;
                $payType = "wechat";
                break;
            case 'alipay_pc_direct':
                $client = false;
                $payType = 'alipay';
                break;
            case 'wx_pub_qr':
                $client = false;
                $payType = 'wechat';
                break;
        }
        $mPlpl->addData([
            'order_no' => $order_no,
            'amount' => $amount,
            'channel' => $channel,
            'status' => 0,
            'client' => $client,
        ]);
        $param = [//支付平台创建订单配置
            'partner' => '1',
            'payway' => $payType,
            'body' => "Pulupulu",
            'subject' => "Pulupulu",
            'total_fee' => $amount / 100,
            'order' => $order_no,
            'notifyurl' => 'http://p.igamer.ren/gopay/notify',
        ];
        $DDM = new DDM($param);
        $DDM->app("1", "A9D9F491VBR11B44");
        $url = $DDM->create()['result']['payurl'];
        $data = HTTP::http_getFile($url, $params, $header);
        echo $data;
        //echo "<script>window.location.href='{$url}';</script>";
    }

    public function notify() {
        $mPlpl = D('plpl');
        $order_no = I('order_no');
        $DDM = new DDM();
        $DDM->app("1", "A9D9F491VBR11B44");
        if (!$DDM->notify("A9D9F491VBR11B44")) {
            return;
        }
        $mPlpl->saveData([
            'order_no' => $order_no
                ], [
            'status' => 1,
        ]);
        HTTP::http_getFile("http://www.pulupulu.com/pay/netbankr", [
            'v_oid' => $order_no, //订单号？
            'v_pstatus' => '20', //状态？
            'v_pstring' => '0', //不晓得
            'v_amount' => '100', //金额
            'v_moneytype' => 'CNY', //类型
            'remark2' => '',
            'v_md5str' => 'plplpay', //校验
                ], null, "POST", false);
        echo 'success';
    }

}
