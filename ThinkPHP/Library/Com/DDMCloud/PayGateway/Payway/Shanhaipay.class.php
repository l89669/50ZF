<?php

namespace Com\DDMCloud\PayGateway\Payway;

use Com\DDMCloud\HTTP;
use Com\DDMCloud\XMLLoader;

class Shanhaipay {

    private $param;

    const SHANHAIPAY_TYPE = array(
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
        'alipay' => 'zfb',
        'wechat' => '32',
        'qq' => '36',
        'tencent' => 'cft',
    );
    const SHANHAIPAY_PAY_ALIPAY = 'http://www.51card.cn/gateway/alipay/alipay.asp';
    const SHANHAIPAY_PAY_TENCENT = 'http://www.51card.cn/gateway/tenpay/tenpay.asp';
    const SHANHAIPAY_PAY_WEIXIN = 'http://www.51card.cn/gateway/weixin/weixinpay.asp';
    const SHANHAIPAY_PAY_QQ = 'http://www.51card.cn/gateway/qqpay/qqpay.asp';
    const SHANHAIPAY_PAY_CARD = NULL;
    const SHANHAIPAY_ORDER_QUERY = 'http://www.51card.cn/gateway/zx_orderquery.asp';

    function __construct($param) {
        $this->param = $param;
    }

    public function setParam($param) {
        $this->param = $param;
        return $this;
    }

    public function reviewType() {
        $tmp = Shanhaipay::SHANHAIPAY_TYPE;
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
        $customerid = C('bank_shanhai_partner');
        $customerkey = C('bank_shanhai_key');
        $sdcustomno = $this->param['order'];
        $orderAmount = $this->param['pay_amount'];
        $cardno = $this->param['pay_type'];
        $noticeurl = C('notify_url') . 'shanhaipay_bank';
        $backurl = C('return_url') . 'shanhaipay_bank/order/' . $sdcustomno;
        $endcustomer = $this->param['order_business'];
        $endip = '127.0.0.1';
        $remarks = "Order" . $sdcustomno;
        $remarks = $remarks;
        $mark = $remarks;
        $signStr = NULL;
        switch ($cardno) {
            case '32'://微信
                $orderAmount = $orderAmount * 100; //以分为单位
                $signStr = "customerid={$customerid}&sdcustomno={$sdcustomno}&orderAmount={$orderAmount}&cardno={$cardno}&noticeurl={$noticeurl}&backurl={$backurl}{$customerkey}";
                $htmlCodePost = '<form name="shanhaipay" action="' . Shanhaipay::SHANHAIPAY_PAY_WEIXIN . '" method="POST">';
                break;
            case '36'://QQ
                $orderAmount = $orderAmount * 100; //以分为单位
                $signStr = "customerid={$customerid}&sdcustomno={$sdcustomno}&orderAmount={$orderAmount}&cardno={$cardno}&noticeurl={$noticeurl}&backurl={$backurl}{$customerkey}";
                $htmlCodePost = '<form name="shanhaipay" action="' . Shanhaipay::SHANHAIPAY_PAY_QQ . '" method="POST">';
                break;
            case 'zfb'://支付宝
                $signStr = "customerid={$customerid}&sdcustomno={$sdcustomno}&ordermoney={$orderAmount}&cardno=34&faceno=zfb&noticeurl={$noticeurl}&endcustomer={$endcustomer}&endip={$endip}&remarks={$remarks} &mark={$mark}&key={$customerkey}";
                $htmlCodePost = '<form name="shanhaipay" action="' . Shanhaipay::SHANHAIPAY_PAY_ALIPAY . '" method="POST">';
                $htmlCodePost = $htmlCodePost . '<input type="hidden" name="faceno" value="zfb">';
                break;
            case 'cft'; //财付通
                $signStr = "customerid={$customerid}&sdcustomno={$sdcustomno}&ordermoney={$orderAmount}&cardno=34&faceno=cft&noticeurl={$noticeurl}&endcustomer={$endcustomer}&endip={$endip}&remarks={$remarks}&mark={$mark}&key={$customerkey}";
                $htmlCodePost = '<form name="shanhaipay" action="' . Shanhaipay::SHANHAIPAY_PAY_TENCENT . '" method="POST">';
                $htmlCodePost = $htmlCodePost . '<input type="hidden" name="faceno" value="cft>';
                break;
        }
        $sign = strtoupper(MD5($signStr));
        switch ($this->param['pay_type']) {
            case 'zfb': {
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="customerid" value="' . $customerid . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="sdcustomno" value="' . $sdcustomno . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="ordermoney" value="' . $orderAmount . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="cardno" value="34">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="noticeurl" value="' . $noticeurl . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="backurl" value="' . $backurl . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="endcustomer" value="' . $endcustomer . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="endip" value="' . $endip . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="remarks" value="' . $remarks . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="mark" value="' . $mark . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="sign" value="' . $sign . '">';
                    $htmlCodePost = $htmlCodePost . '</form>';
                    break;
                }
            case 'cft': {
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="customerid" value="' . $customerid . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="sdcustomno" value="' . $sdcustomno . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="ordermoney" value="' . $orderAmount . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="cardno" value="34">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="noticeurl" value="' . $noticeurl . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="backurl" value="' . $backurl . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="endcustomer" value="' . $endcustomer . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="endip" value="' . $endip . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="remarks" value="' . $remarks . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="mark" value="' . $mark . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="sign" value="' . $sign . '">';
                    $htmlCodePost = $htmlCodePost . '</form>';
                    break;
                }
            case '32': {
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="customerid" value="' . $customerid . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="sdcustomno" value="' . $sdcustomno . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="orderAmount" value="' . $orderAmount . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="cardno" value="' . $cardno . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="noticeurl" value="' . $noticeurl . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="backurl" value="' . $backurl . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="sign" value="' . $sign . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="mark" value="' . $mark . '">';
                    $htmlCodePost = $htmlCodePost . '</form>';
                    break;
                }
            case '36': {
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="customerid" value="' . $customerid . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="sdcustomno" value="' . $sdcustomno . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="orderAmount" value="' . $orderAmount . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="cardno" value="' . $cardno . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="noticeurl" value="' . $noticeurl . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="backurl" value="' . $backurl . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="sign" value="' . $sign . '">';
                    $htmlCodePost = $htmlCodePost . '<input type="hidden" name="mark" value="' . $mark . '">';
                    $htmlCodePost = $htmlCodePost . '</form>';
                    break;
                }
        }
        $html = '<html>';
        $html = $html . ' <head id="Head1">';
        $html = $html . ' <title>正在连接网关, 请稍候...</title>';
        $html = $html . ' </head>';
        $html = $html . ' <body>正在连接网关, 请稍候...</body>';
        $html = $html . ' <body onload="document.shanhaipay.submit()">';
        $html = $html . $htmlCodePost;
        $html = $html . ' </body>';
        $html = $html . '</html>';
        return $html;
    }

    public static function notify() {
        $state = I('state');
        $customerid = C('bank_shanhai_partner');
        $customerkey = C('bank_shanhai_key');
        $sd51no = I('sd51no');
        $sdcustomno = I('sdcustomno');
        $ordermoney = I('ordermoney');
        $cardno = I('cardno');
        $mark = I('mark');
        $sign = I('sign');
        $resign = I('resign');
        $des = I('des');
        //
        if ($cardno == '34') {
            $signStr = "customerid={$customerid}&sd51no={$sd51no}&sdcustomno={$sdcustomno}&key={$customerkey}";
            $resignStr = "sign={$sign}&customerid={$customerid}&ordermoney={$ordermoney}&sd51no={$sd51no}&state={$state}&key={$customerkey}";
            if (($sign == strtoupper(MD5($signStr))) && ($resign == strtoupper(MD5($resignStr)))) {
                return true;
            }
        }
        //这家罪恶的API提供商的开发文档都是垃圾。。。实在没办法写Notify加密判断。。。直接从服务器取订单信息了吧。。。。
        $Shanhaipay = new Shanhaipay(array('order' => $sdcustomno));
        $result = $Shanhaipay->query();
        if ($result['state'] != '1') {//状态错误
            return false;
        }
        if ($result['ordermoney'] < $ordermoney) {//金额错误
            return false;
        }
        return true;
    }

    public static function back() {//出现问题暂时停用
        $customerid = C('bank_shanhai_partner');
        $customerkey = C('bank_shanhai_key');
        $sdcustomno = I('sdcustomno');
        $state = I('state');
        $sd51no = I('sd51no');
        $sign = I('sign');
        $signStr = "sdcustomno={$sdcustomno}&state={$state}&sd51no={$sd51no}&key={$customerkey}";
        echo "$signStr<br>" . strtoupper(MD5($signStr)) . "<br>";
        if ($sign != strtoupper(MD5($signStr))) {
            return false;
        }
        return true;
    }

    public function query() {
        $customerid = C('bank_shanhai_partner');
        $customerkey = C('bank_shanhai_key');
        $sdcustomno = $this->param['order'];
        $signStr = "customerid={$customerid}&sdcustomno={$sdcustomno}&mark={$mark}&key={$customerkey}";
        $sign = strtoupper(MD5($signStr));
        $url = Shanhaipay::SHANHAIPAY_ORDER_QUERY . "?customerid={$customerid}&sdcustomno={$sdcustomno}&mark={$mark}&key={$customerkey}&sign={$sign}";
        $data = HTTP::http_getFile($url, $params, $header);
        $args = XMLLoader::xml_to_array($data);
        $return = array(
            'state' => $args['items']['item']['0']['@attributes']['value'],
            'sd51no' => $args['items']['item']['1']['@attributes']['value'],
            'cardno' => $args['items']['item']['2']['@attributes']['value'],
            'ordermoney' => $args['items']['item']['3']['@attributes']['value'],
            'mark' => $args['items']['item']['4']['@attributes']['value'],
        );
        return $return;
    }

}
