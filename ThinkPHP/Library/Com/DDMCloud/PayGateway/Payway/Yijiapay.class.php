<?php

namespace Com\DDMCloud\PayGateway\Payway;

class Yijiapay {

    private $param;

    const YIJIAPAY_TYPE = array(
        1 => NULL,
        2 => NULL,
        3 => NULL,
        4 => NULL,
        5 => NULL,
        6 => NULL,
        7 => NULL,
        8 => NULL,
        9 => NULL,
        10 => NULL,
        11 => NULL,
        12 => NULL,
        13 => NULL,
        14 => NULL,
        15 => NULL,
        16 => NULL,
        17 => NULL,
        18 => NULL,
        19 => NULL,
        'alipay' => 'ALIPAY',
        'wechat' => 'WEIXINPAY',
        'qq' => 'TENPAY',
        'tencent' => 'TENPAY',
    );
    const YIJIAPAY_PAY_BANK = 'http://www.yijiapay.net/interace/gateway';
    const YIJIAPAY_PAY_CARD = '';

    function __construct($param) {
        $this->param = $param;
    }

    public function setParam($param) {
        $this->param = $param;
        return $this;
    }

    public function reviewType() {
        $tmp = Yijiapay::YIJIAPAY_TYPE;
        if ($this->param['pay_way'] == 'card') {
            $type = $this->param['pay_type'];
            $this->param['pay_type'] = $tmp[$type];
            return $this;
        }
        $type = $this->param['pay_way'];
        $this->param['pay_type'] = $tmp[$type];
        return $this;
    }

    public function goPay() {
        $this->reviewType();
        if ($this->param['pay_way'] == 'card') {
            return $this->goPay_Card();
        }
        return $this->goPay_Bank();
    }

    public function goPay_Card() {
        
    }

    public function goPay_Bank() {
        $merchantid = C('bank_yijiapay_partner');
        $merckey = C('bank_yijiapay_key');

        $orderid = $this->param['order'];
        $money = $this->param['pay_amount'];
        $channeltype = $this->param['pay_type'];
        $bankcode = '';
        $cardid = '';
        $cardpass = '';
        $notifyurl = C('notify_url') . 'yijiapay_bank';
        $resulturl = C('return_url') . 'yijiapay_bank/order/' . $orderid;
        $custom = '';
        $signStr = "merchantid={$merchantid}&orderid={$orderid}&money={$money}&channeltype={$channeltype}&bankcode={$bankcode}&cardid={$cardid}&cardpass={$cardpass}&notifyurl={$notifyurl}&resulturl={$resulturl}&merchantkey={$merckey}";
        $sign = MD5($signStr);
        $htmlCodePost = '<form name="yijiapay" action="' . Yijiapay::YIJIAPAY_PAY_BANK . '" method="POST">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="merchantid" value="' . $merchantid . '">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="orderid" value="' . $orderid . '">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="money" value="' . $money . '">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="channeltype" value="' . $channeltype . '">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="bankcode" value="' . $bankcode . '">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="cardid" value="' . $cardid . '">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="cardpass" value="' . $cardpass . '">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="notifyurl" value="' . $notifyurl . '">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="resulturl" value="' . $resulturl . '">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="sign" value="' . $sign . '">';
        $htmlCodePost = $htmlCodePost . '</form>';
        $html = '<html>';
        $html = $html . ' <head id="Head1">';
        $html = $html . '   <title>正在连接网关,请稍候...</title>';
        $html = $html . ' </head>';
        $html = $html . ' <body>正在连接网关,请稍候...</body>';
        $html = $html . ' <body onload="document.yijiapay.submit()">';
        $html = $html . $htmlCodePost;
        $html = $html . ' 	</body>';
        $html = $html . '</html>';
        return $html;
    }

    public static function notify() {
        $merchantid = C('bank_yijiapay_partner');
        $merckey = C('bank_yijiapay_key');

        $errcode = I('errcode');
        $myorderno = I('myorderno');
        $orderno = I('orderno');
        $amount = I('amount');
        $realamount = I('realamount');
        $ordertime = I('ordertime');
        $custom = I('custom');
        $errmsg = I('errmsg');
        $sign = I('sign');
        $signStr = "merchantid={$merchantid}&errcode={$errcode}&amount={$amount}&realamount={$realamount}&myorderno={$myorderno}&orderno={$orderno}&ordertime={$ordertime}&merchantkey={$merckey}";
        if ($sign == MD5($signStr)) {
            return true;
        }
        return false;
    }

}
