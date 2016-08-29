<?php

namespace Com\DDMCloud\PayGateway\Payway;

use Com\DDMCloud\HTTP;
use Com\DDMCloud\XMLLoader;
use Com\DDMCloud\PayGateway\Logs;

class i3ka {

    private $param;

    const I3KA_TYPE = array(
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
        6 => 6,
        7 => 7,
        8 => 8,
        9 => 9,
        10 => 10,
        11 => 11,
        12 => 12,
        13 => 13,
        14 => 14,
        15 => 15,
        16 => 16,
        17 => 17,
        18 => 18,
        19 => 19,
        20 => 20,
        21 => 21,
    );
    const I3KA_PAY = 'http://usb.i3ka.com/interface/CardReceive.aspx';

    function __construct($param) {
        $this->param = $param;
    }

    public function setParam($param) {
        $this->param = $param;
        return $this;
    }

    public function reviewType() {
        $tmp = self::I3KA_TYPE;
        $type = $this->param['pay_type'];
        $this->param['pay_type'] = $tmp[$type];
        return $this;
    }

    public function goPay() {
        $this->reviewType();
        $cardtype = $this->param['pay_type'];
        $parter = C('card_i3ka_parter');
        $cardno = $this->param['cardno'];
        $cardpwd = $this->param['cardpwd'];
        $price = $this->param['pay_amount'];
        $restrict = 0;
        $orderid = $this->param['order'];
        $callbackurl = C('notify_url') . 'i3ka';
        $key = C('card_i3ka_key');
        //=====================
        $signstr = "parter={$parter}&cardtype={$cardtype}&cardno={$cardno}&cardpwd={$cardpwd}&orderid={$orderid}&callbackurl={$callbackurl}&restrict={$restrict}&price={$price}{$key}";
        $sign = MD5($signstr);
        $params = "?";
        $params.="cardtype=$cardtype&";
        $params.="parter=$parter&";
        $params.="cardno=$cardno&";
        $params.="cardpwd=$cardpwd&";
        $params.="price=$price&";
        $params.="restrict=$restrict&";
        $params.="orderid=$orderid&";
        $params.="callbackurl=$callbackurl&";
        $params.="sign=$sign";
        $url = self::I3KA_PAY . $params;
        $result = HTTP::http_getFile($url, NULL, NULL);
        $result = iconv("gb2312", "utf-8//IGNORE", $result); //转码
        //echo $url;
        $result = explode(':', $result);
        $return['error'] = '0';
        $return['result']['order'] = $orderid;
        $return['result']['error'] = $result[0];
        $return['result']['reason'] = $result[1];
        if (L("pay_error_i3ka_{$result[0]}") != "PAY_ERROR_I3KA_{$result[0]}") {
            $return['result']['reason'] = L("pay_error_i3ka_{$result[0]}");
        }
        return json_encode($return, JSON_UNESCAPED_UNICODE);
    }

    public static function notify($params) {
        $key = C('card_i3ka_key');
        $orderid = $params['orderid'];
        $restate = $params['restate'];
        $ovalue = $params['ovalue'];
        $attach = $params['attach'];
        $sign = MD5("orderid={$orderid}&restate={$restate}&ovalue={$ovalue}{$key}");
        if ($params['sign'] != $sign) {
            return false;
        }
        return true;
    }

    public function setCardNo($cardNo) {
        $this->param['cardno'] = $cardNo;
        return $this;
    }

    public function setCardPwd($cardPwd) {
        $this->param['cardpwd'] = $cardPwd;
        return $this;
    }

}
