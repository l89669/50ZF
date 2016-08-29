<?php

namespace Com\DDMCloud\PayGateway\Payway;

use Com\DDMCloud\HTTP;
use Com\DDMCloud\XMLLoader;

class Iwmpay {

    private $param;

    const IWMPAY_TYPE = array(
        'alipay' => 'alipay',
        'qq' => 'qq',
        'qqm' => 'qqm',
        'wechat' => 'wechat',
        'wechatm' => 'wechatm',
        'tencent' => 'tencent',
    );
    const IWMPAY_PAY_ALIPAY = 'http://vip.wmcard.cn/intf/npay.html';
    const IWMPAY_PAY_TENCENT = 'http://vip.wmcard.cn/intf/tpay.html';
    const IWMPAY_PAY_WECHAT = 'http://vip.wmcard.cn/intf/wpay.html';
    const IWMPAY_PAY_WECHAT_M = 'http://vip.wmcard.cn/intf/wapwpay.html';
    const IWMPAY_PAY_QQ = 'http://vip.wmcard.cn/intf/spay.html';
    const IWMPAY_PAY_QQ_M = 'http://vip.wmcard.cn/intf/wapspay.html';

    function __construct($param) {
        $this->param = $param;
    }

    public function setParam($param) {
        $this->param = $param;
        return $this;
    }

    public function setClient($bool) {
        if ($bool == null) {
            $bool = I('client');
        }
        if ($bool) {
            switch ($this->param['pay_way']) {
                case 'qq':
                    $this->param['pay_way'] = 'qqm';
                    break;
                case 'wechat':
                    $this->param['pay_way'] = 'wechatm';
                    break;
                default:
                    break;
            }
            return;
        }
        switch ($this->param['pay_way']) {
            case 'qqm':
                $this->param['pay_way'] = 'qq';
                break;
            case 'wechatm':
                $this->param['pay_way'] = 'wechat';
                break;
            default:
                break;
        }
        return;
    }

    public function reviewType() {//该渠道点卡费率太低 禁用！！
        $tmp = Iwmpay::IWMPAY_TYPE;
        $type = $this->param['pay_way'];
        $this->param['pay_type'] = $tmp[$type];
        $this->setClient();
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
        $way = $this->param['pay_way'];
        switch ($way) {
            case 'alipay':
                return $this->goPay_Alipay();
            case 'tencent':
                return $this->goPay_Tencent();
            default:
                return $this->goPay_Shanhai();
        }
    }

    public function goPay_Alipay() {
        $url = Iwmpay::IWMPAY_PAY_ALIPAY;
        $key = C('bank_iwmpay_key');
        $_input_charset = 'gbk';
        $default_login = 'N';
        $exter_invoke_ip = '127.0.0.1';
        $notify_url = C('notify_url') . 'iwmpay_bank/pay_way/alipay';
        $out_trade_no = $this->param['order'];
        $partner = C('bank_iwmpay_partner');
        $payment_type = '1';
        $seller_email = C('alipay_account');
        $service = 'create_direct_pay_by_user';
        $subject = 'product';
        $total_fee = $this->param['pay_amount'];
        $sign_typ = 'MD5';
        $signStr = "_input_charset={$_input_charset}&default_login={$default_login}&exter_invoke_ip={$exter_invoke_ip}&notify_url={$notify_url}&out_trade_no={$out_trade_no}&partner={$partner}&payment_type={$payment_type}&seller_email={$seller_email}&service={$service}&subject={$subject}&total_fee={$total_fee}{$key}";
        $sign = MD5($signStr);
        $html = <<<Eof
<html> <head id="Head1">
    <title>正在连接网关, 请稍候...</title>
        </head> <body>正在连接网关, 请稍候...</body>
        <body onload="document.iwmpay.submit()">
        <form name="iwmpay" action="{$url}" method="POST">
        <input type="hidden" name="_input_charset" value="{$_input_charset}">
        <input type="hidden" name="default_login" value="{$default_login}">
        <input type="hidden" name="exter_invoke_ip" value="{$exter_invoke_ip}">
        <input type="hidden" name="notify_url" value="{$notify_url}">
        <input type="hidden" name="out_trade_no" value="{$out_trade_no}">
        <input type="hidden" name="partner" value="{$partner}">
        <input type="hidden" name="payment_type" value="{$payment_type}">
        <input type="hidden" name="seller_email" value="{$seller_email}">
        <input type="hidden" name="service" value="{$service}">
        <input type="hidden" name="subject" value="{$subject}">
        <input type="hidden" name="total_fee" value="{$total_fee}">
        <input type="hidden" name="sign_type" value="{$sign_typ}">
        <input type="hidden" name="sign" value="{$sign}">
        </form> 
        </body>
</html>
Eof;
        return $html;
    }

    public function goPay_Tencent() {
        $url = Iwmpay::IWMPAY_PAY_TENCENT;
        $key = C('bank_iwmpay_key');
        $cmdno = "1";
        $date = date('Ymd');
        $bank_type = "0";
        $sp_billno = $this->param['order'];
        $desc = urlencode("订单{$sp_billno}");
        $purchaser_id = "";
        $bargainor_id = C('bank_iwmpay_partner');
        $transaction_id = $sp_billno;
        $total_fee = $this->param['pay_amount'] * 100;
        $fee_type = "1";
        $return_url = C('notify_url') . 'iwmpay_bank/pay_way/tencent';
        $attach = 'cft';
        $spbill_create_ip = '127.0.0.1';
        $cs = 'utf-8';
        $signStr = "cmdno={$cmdno}&date={$date}&bargainor_id={$bargainor_id}&transaction_id={$transaction_id}&sp_billno={$sp_billno}&total_fee={$total_fee}&fee_type={$fee_type}&return_url={$return_url}&attach={$attach}&spbill_create_ip={$spbill_create_ip}&key={$key}";
        $sign = MD5($signStr);
        $html = <<<Eof
<html> <head id="Head1">
    <title>正在连接网关, 请稍候...</title>
        </head> <body>正在连接网关, 请稍候...</body>
        <body onload="document.iwmpay.submit()">
        <form name="iwmpay" action="{$url}" method="POST">
        <input type="hidden" name="cmdno" value="{$cmdno}">
        <input type="hidden" name="date" value="{$date}">
        <input type="hidden" name="bank_type" value="{$bank_type}">
        <input type="hidden" name="desc" value="{$desc}">
        <input type="hidden" name="purchaser_id" value="{$purchaser_id}">
        <input type="hidden" name="bargainor_id" value="{$bargainor_id}">
        <input type="hidden" name="transaction_id" value="{$transaction_id}">
        <input type="hidden" name="sp_billno" value="{$sp_billno}">
        <input type="hidden" name="total_fee" value="{$total_fee}">
        <input type="hidden" name="fee_type" value="{$fee_type}">
        <input type="hidden" name="return_url" value="{$return_url}">
        <input type="hidden" name="attach" value="{$attach}">
        <input type="hidden" name="spbill_create_ip" value="{$spbill_create_ip}">
        <input type="hidden" name="sign" value="{$sign}">
        <input type="hidden" name="cs" value="{$cs}">
        </form> 
        </body>
</html>
Eof;
        return $html;
    }

    public function goPay_Shanhai() {
        switch ($this->param['pay_way']) {
            case 'wechat': {
                    $url = Iwmpay::IWMPAY_PAY_WECHAT;
                    break;
                }
            case 'wechatm': {
                    $url = Iwmpay::IWMPAY_PAY_WECHAT_M;
                    break;
                }
            case 'qq': {
                    $url = Iwmpay::IWMPAY_PAY_QQ;
                    break;
                }
            case 'qqm': {
                    $url = Iwmpay::IWMPAY_PAY_QQ_M;
                    break;
                }
        }
        $customerid = C('bank_iwmpay_partner');
        $key = C('bank_iwmpay_key');
        $sdcustomno = $this->param['order'];
        $orderAmount = $this->param['pay_amount'] * 100;
        $cardno = '32';
        $noticeurl = C('notify_url') . 'iwmpay_bank/pay_way/shanhai';
        $backurl = C('return_url') . 'iwmpay_bank/order/' . $sdcustomno;
        $mark = "Order{$sdcustomno}";
        $signStr = "customerid={$customerid}&sdcustomno={$sdcustomno}&orderAmount={$orderAmount}&cardno={$cardno}&noticeurl={$noticeurl}&backurl={$backurl}{$key}";
        $sign = strtoupper(MD5($signStr));
        $html = <<<Eof
<html> <head id="Head1">
    <title>正在连接网关, 请稍候...</title>
        </head> <body>正在连接网关, 请稍候...</body>
        <body onload="document.iwmpay.submit()">
        <form name="iwmpay" action="{$url}" method="POST">
        <input type="hidden" name="customerid" value="{$customerid}">
        <input type="hidden" name="sdcustomno" value="{$sdcustomno}">
        <input type="hidden" name="orderAmount" value="{$orderAmount}">
        <input type="hidden" name="cardno" value="{$cardno}">
        <input type="hidden" name="noticeurl" value="{$noticeurl}">
        <input type="hidden" name="backurl" value="{$backurl}">
        <input type="hidden" name="sign" value="{$sign}">
        <input type="hidden" name="mark" value="{$mark}">
        </form> 
        </body>
</html>
Eof;
        return $html;
    }

    public static function notify($pay_way) {//等待
        if (!$pay_way) {
            $pay_way = I('get.pay_way');
        }
        switch ($pay_way) {
            case 'alipay':
                return self::notify_Alipay();
            case 'tencent':
                return self::notify_Tencent();
            default:
                return self::notify_Shanhai();
        }
        return false;
    }

    public static function notify_Alipay() {
        $apiKey = C('bank_iwmpay_key');
        $body = I('body');
        $buyer_email = I('buyer_email');
        $buyer_id = I('buyer_id');
        $discount = I('discount');
        $extra_common_param = I('extra_common_param');
        $gmt_close = I('gmt_close');
        $gmt_create = I('gmt_create');
        $gmt_payment = I('gmt_payment');
        $gmt_refund = I('gmt_refund');
        $is_total_fee_adjust = I('is_total_fee_adjust');
        $notify_id = I('notify_id');
        $notify_time = I('notify_time');
        $notify_type = I('notify_type');
        $out_trade_no = I('out_trade_no');
        $payment_type = I('payment_type');
        $price = I('price');
        $quantity = I('quantity');
        $refund_status = I('refund_status');
        $seller_email = I('seller_email');
        $seller_id = I('seller_id');
        $sign = I('sign');
        $sign_type = I('sign_type');
        $subject = I('subject');
        $total_fee = I('total_fee');
        $trade_no = I('trade_no');
        $trade_status = I('trade_status');
        $use_coupon = I('use_coupon');
        $paramsArr = array(
            'body' => $body,
            'buyer_email' => $buyer_email,
            'buyer_id' => $buyer_id,
            'discount' => $discount,
            'extra_common_param' => $extra_common_param,
            'gmt_close' => $gmt_close,
            'gmt_create' => $gmt_create,
            'gmt_payment' => $gmt_payment,
            'gmt_refund' => $gmt_refund,
            'is_total_fee_adjust' => $is_total_fee_adjust,
            'notify_id' => $notify_id,
            'notify_time' => $notify_time,
            'notify_type' => $notify_type,
            'out_trade_no' => $out_trade_no,
            'payment_type' => $payment_type,
            'price' => $price,
            'quantity' => $quantity,
            'refund_status' => $refund_status,
            'seller_email' => $seller_email,
            'seller_id' => $seller_id,
            'subject' => $subject,
            'total_fee' => $total_fee,
            'trade_no' => $trade_no,
            'trade_status' => $trade_status,
            'use_coupon' => $use_coupon,
        ); //已经预排序
        foreach ($paramsArr as $key => $value) {
            $i++;
            if ($value==null) {
                continue;
            }
            $tmp[$key] = $value;
        }
        $i = 0;
        foreach ($tmp as $key => $value) {
            $i++;
            $signStr .= "{$key}={$value}";
            if ($i < count($tmp)) {
                $signStr .= "&";
            }
        }
        $signStr .= $apiKey;
        $signStr = mb_convert_encoding($signStr, 'gbk');
        //echo $signStr;
        if ($sign == MD5($signStr)) {
            return true;
        }
        return false;
    }

    public static function notify_Tencent() {
        $key = C('bank_iwmpay_key');
        $cmdno = I('cmdno');
        $pay_result = I('pay_result');
        $pay_info = I('pay_info');
        $date = I('date');
        $bargainor_id = I('bargainor_id');
        $transaction_id = I('transaction_id');
        $sp_billno = I('sp_billno');
        $total_fee = I('total_fee');
        $fee_type = I('fee_type');
        $attach = I('attach');
        $sign = I('sign');
        $signStr = "cmdno={$cmdno}&pay_result={$pay_result}&date={$date}&transaction_id={$transaction_id}&sp_billno={$sp_billno}&total_fee={$total_fee}&fee_type={$fee_type}&attach={$attach}&key={$key}";
        if ($sign == strtoupper(MD5($signStr))) {
            return true;
        }
        return false;
    }

    public static function notify_Shanhai() {
        $key = C('bank_iwmpay_key');
        $state = I('state');
        $customerid = I('customerid');
        $sd51no = I('sd51no');
        $sdcustomno = I('sdcustomno');
        $ordermoney = I('ordermoney');
        $cardno = I('cardno');
        $mark = I('mark');
        $sign = I('sign');
        $resign = I('resign');
        $des = I('des');
        $signStr = "customerid={$customerid}&sd51no={$sd51no}&sdcustomno={$sdcustomno}&mark={$mark}&key={$key}";
        if ($sign != strtoupper(MD5($signStr))) {
            return false;
        }
        $resignStr = "sign={$sign}&customerid={$customerid}&ordermoney={$ordermoney}&sd51no={$sd51no}&state={$state}&key={$key}";
        if ($resign != strtoupper(MD5($resignStr))) {
            return false;
        }
        return true;
    }

    public static function back() {//渠道无查询方法 直接返回备用
        return true;
    }

    public static function getParams($pay_way) {
        if (!$pay_way) {
            $pay_way = I('get.pay_way');
        }
        switch ($pay_way) {
            case 'alipay':
                $out_trade_no = I('out_trade_no');
                $notify_time = I('notify_time');
                $notify_type = I('notify_type');
                $notify_id = I('notify_id');
                $sign_type = I('sign_type');
                $subject = I('subject');
                $payment_type = I('payment_type');
                $trade_no = I('trade_no');
                $trade_status = I('trade_status');
                $gmt_create = I('gmt_create');
                $gmt_payment = I('gmt_payment');
                $gmt_close = I('gmt_close');
                $refund_status = I('refund_status');
                $gmt_refund = I('gmt_refund');
                $seller_email = I('seller_email');
                $buyer_email = I('buyer_email');
                $seller_id = I('seller_id');
                $buyer_id = I('buyer_id');
                $price = I('price');
                $total_fee = I('total_fee');
                $quantity = I('quantity');
                $body = I('body');
                $discount = I('discount');
                $is_total_fee_adjust = I('is_total_fee_adjust');
                $use_coupon = I('use_coupon');
                $extra_common_param = I('extra_common_param');
                $Sign = I('Sign');
                break;
            case 'tencent':
                $cmdno = I('cmdno');
                $pay_result = I('pay_result');
                $pay_info = I('pay_info');
                $date = I('data');
                $bargainor_id = I('bargainor_id');
                $transaction_id = I('transaction_id');
                $sp_billno = I('sp_billno');
                $total_fee = I('total_fee');
                $fee_type = I('fee_type');
                $attach = I('attach');
                $sign = I('sign');
            default:
                $state = I('state');
                $customerid = I('customerid');
                $sd51no = I('sd51no');
                $sdcustomno = I('sdcustomno');
                $ordermoney = I('ordermoney');
                $cardno = I('cardno');
                $mark = I('mark');
                $sign = I('sign');
                $resign = I('resign');
                $des = I('des');
        }
        $params = array();
        switch ($pay_way) {
            case 'alipay':
                $params['order'] = $out_trade_no;
                if ($trade_status == "TRADE_SUCCESS") {
                    $params['status'] = true;
                } else {
                    $params['status'] = false;
                }
                $params['pay_amount'] = $total_fee;
                $params['pay_time'] = $gmt_payment;
                break;
            case 'tencent':
                $params['order'] = $sp_billno;
                if ($pay_result == "0") {
                    $params['status'] = true;
                } else {
                    $params['status'] = false;
                }
                $params['pay_amount'] = $total_fee;
                $params['pay_time'] = $date;
                break;
            default:
                $params['order'] = $sdcustomno;
                if ($state == "1") {
                    $params['status'] = true;
                } else {
                    $params['status'] = false;
                }
                $params['pay_amount'] = $ordermoney;
                $params['pay_time'] = '0';
                break;
        }
        return $params;
    }

}
