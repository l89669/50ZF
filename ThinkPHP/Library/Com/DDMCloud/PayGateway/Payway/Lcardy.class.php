<?php

namespace Com\DDMCloud\PayGateway\Payway;

class Lcardy {

    private $param;

    const LCARDY_TYPE = array(
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
        17 => 'TELECOM',
        18 => 'SZX',
        19 => 'UNICOM',
        'alipay' => 'ALIPAY',
        'wechat' => 'WEIXINPAY',
        'qq' => 'TENPAY',
        'tencent' => 'TENPAY',
    );
    const LCARDY_PAY_BANK = 'http://interface.lcardy.com/Online_Banking_interface';
    const LCARDY_PAY_CARD = 'http://interface.lcardy.com/Merchant_Proxy_Interface';

    function __construct($param) {
        $this->param = $param;
    }

    public function setParam($param) {
        $this->param = $param;
        return $this;
    }

    public function reviewType() {
        $tmp = Lcardy::LCARDY_TYPE;
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
        $mercid = C('card_lcardy_partner');
        $merckey = C('card_lcardy_key');
        $businesstype = 'directpay';
        $version = 'V2.0';
        $signtype = 'MD5';
        $orderno = $this->param['order'];

        $pamount = $this->param['pay_amount'];

        $meurl = C('notify_url') . 'lcardy_bank';
        $pageurl = C('return_url') . 'lcardy_bank/order/' . $orderno;
        $bacode = $this->param['pay_type'];

        $exinf = '';

        $tracur = 'CNY';
        $proname = "订单 " . $orderno . " 支付.";
        $cdit = $proname;
        $deion = $proname;
        $mercip = '127.0.0.1';
        $productnum = '1';
        $ordertime = date("Y-m-d H:i:s");

        $signStr = 'mercid=' . $mercid;
        $signStr = $signStr . '&orderno=' . $orderno;
        $signStr = $signStr . '&ordertime=' . $ordertime;
        $signStr = $signStr . '&pamount=' . $pamount;
        $signStr = $signStr . '&meurl=' . $meurl;
        $signStr = $signStr . '&pageurl=' . $pageurl;
        $signStr = $signStr . '&bacode=' . $bacode;
        $signStr = $signStr . '&tracur=' . $tracur;
        $signStr = $signStr . '&proname=' . $proname;
        $signStr = $signStr . '&mercip=' . $mercip;
        $signStr = $signStr . '&businesstype=' . $businesstype;
        $signStr = $signStr . '&version=' . $version;
        $signStr = $signStr . '&signtype=' . $signtype;
        $signStr = $signStr . '&merckey=' . $merckey;
        $sign = MD5($signStr);
        $htmlCodePost = '<form name="lcardy" action="' . Lcardy::LCARDY_PAY_BANK . '" method="POST">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="mercid" value="' . $mercid . '">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="orderno" value="' . $orderno . '">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="pamount" value="' . $pamount . '">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="meurl" value="' . $meurl . '">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="pageurl" value="' . $pageurl . '">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="bacode" value="' . $bacode . '">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="tracur" value="' . $tracur . '">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="proname" value="' . $proname . '">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="businesstype" value="' . $businesstype . '">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="version" value="' . $version . '">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="signtype" value="' . $signtype . '">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="exinf" value="' . $exinf . '">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="sign" value="' . $sign . '">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="ordertime" value="' . $ordertime . '">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="cdit" value="' . $cdit . '">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="deion" value="' . $deion . '">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="productnum" value="' . $productnum . '">';
        $htmlCodePost = $htmlCodePost . '<input type="hidden" name="mercip" value="' . $mercip . '">';
        $htmlCodePost = $htmlCodePost . '</form>';
        $html = '<html>';
        $html = $html . ' <head id="Head1">';
        $html = $html . '   <title>正在连接网关,请稍候...</title>';
        $html = $html . ' </head>';
        $html = $html . ' <body>正在连接网关,请稍候...</body>';
        $html = $html . ' <body onload="document.lcardy.submit()">';
        $html = $html . $htmlCodePost;
        $html = $html . ' 	</body>';
        $html = $html . '</html>';
        return $html;
    }

    public static function notify() {
        $mercid = $_REQUEST["mercid"];
        $merckey = C('card_lcardy_key');
        $version = $_REQUEST["version"];
        $signtype = $_REQUEST["signtype"];
        $code = $_REQUEST["code"];
        $myorderno = $_REQUEST["myorderno"];
        $orderamount = $_REQUEST["orderamount"];
        $orderno = $_REQUEST["orderno"];
        $ordertime = $_REQUEST["ordertime"];
        $exinf = $_REQUEST["exinf"];
        $notifytype = $_REQUEST["notifytype"];
        $bankseqno = $_REQUEST["bankseqno"];
        $sign = $_REQUEST["sign"];
        $signStr = 'mercid=' . $mercid;
        $signStr = $signStr . '&code=' . $code;
        $signStr = $signStr . '&myorderno=' . $myorderno;
        $signStr = $signStr . '&orderamount=' . $orderamount;
        $signStr = $signStr . '&orderno=' . $orderno;
        $signStr = $signStr . '&ordertime=' . $ordertime;
        $signStr = $signStr . '&signtype=' . $signtype;
        $signStr = $signStr . '&version=' . $version;
        $signStr = $signStr . '&notifytype=' . $notifytype;
        $signStr = $signStr . '&bankseqno=' . $bankseqno;
        $signStr = $signStr . '&merckey=' . $merckey;
        $signValue = MD5($signStr);
        if ($signValue == $sign) {
            if ($code == "1") {
                return true;
            } else {
                return false;
            }
        }
    }

}
