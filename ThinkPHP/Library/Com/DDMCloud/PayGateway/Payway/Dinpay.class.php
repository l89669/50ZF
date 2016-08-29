<?php

namespace Com\DDMCloud\PayGateway\Payway;

use Com\DDMCloud\HTTP;
use Com\DDMCloud\XMLLoader;
use Com\DDMCloud\PayGateway\Logs;

class Dinpay {

    private $param;

    const DINPAY_TYPE = array(
        'ABC' => 'ABC',
        'ICBC' => 'ICBC',
        'CCB' => 'CCB',
        'BCOM' => 'BCOM',
        'BOC' => 'BOC',
        'CMB' => 'CMB',
        'CMBC' => 'CMBC',
        'CEBB' => 'CEBB',
        'BOB' => 'BOB',
        'SHB' => 'SHB',
        'NBB' => 'NBB',
        'HXB' => 'HXB',
        'CIB' => 'CIB',
        'PSBC' => 'PSBC',
        'SPABANK' => 'SPABANK',
        'SPDB' => 'SPDB',
        'ECITIC' => 'ECITIC',
        'BEA' => 'BEA',
        'BHB' => 'BHB',
        'HSBANK' => 'HSBANK',
    );

    function __construct($param) {
        $this->param = $param;
    }

    public function setParam($param) {
        $this->param = $param;
        return $this;
    }

    public function reviewType() {
        $tmp = self::DINPAY_TYPE;
        $type = $this->param['pay_type'];
        $this->param['pay_type'] = $tmp[$type];
        return $this;
    }

    public function goPay() {
        global $merchant_code, $service_type, $interface_version, $sign_type, $input_charset, $notify_url, $order_no, $order_time, $order_amount, $product_name, $bank_code, $return_url;
        $merchant_code = C('bank_business');
        $service_type = "direct_pay";
        $interface_version = "V3.0";
        $sign_type = "RSA-S";
        $input_charset = "UTF-8";
        $bank_code = $this->param['pay_type'];
        $order_no = $this->param['order'];
        $return_url = C('return_url') . 'dinpay/order_no/' . $order_no;
        $notify_url = C('notify_url') . 'dinpay';
        $order_time = date("Y-m-d H:i:s");
        $order_amount = $this->param['pay_amount'];
        $product_name = "订单 " . $order_no . " 支付.";
        $sign = $this->getSign();
        $html = "<html>
	<head>
		<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
	</head>	
	<body onLoad='document.dinpayForm.submit();'>
		<form name='dinpayForm' method='post' action='https://pay.dinpay.com/gateway?input_charset=UTF-8' target='_self'>
			<input type='hidden' name='sign'          value='$sign' />
			<input type='hidden' name='merchant_code' value='$merchant_code' />
			<input type='hidden' name='bank_code'     value='$bank_code'/>
			<input type='hidden' name='order_no'      value='$order_no'/>
			<input type='hidden' name='order_amount'  value='$order_amount'/>
			<input type='hidden' name='service_type'  value='$service_type'/>
			<input type='hidden' name='input_charset' value='$input_charset'/>
			<input type='hidden' name='notify_url'    value='$notify_url'>
			<input Type='hidden' Name='return_url'    value='$return_url'/>
			<input type='hidden' name='interface_version' value='$interface_version'/>
			<input type='hidden' name='sign_type'     value='$sign_type'/>
			<input type='hidden' name='order_time'    value='$order_time'/>
			<input type='hidden' name='product_name'  value='$product_name'/>
		</form>
	</body>
</html>";
        Logs::logDefault('payorder', "Order $order_no use bank(dinpay-$bank_code) to pay", NULL, $order_no);
        return $html;
    }

    private function getSign() {
        global $merchant_code, $service_type, $interface_version, $sign_type, $input_charset, $notify_url, $order_no, $order_time, $order_amount, $product_name, $bank_code, $return_url;
        $priKey = openssl_get_privatekey(C('bank_private'));
        if ($bank_code != "") {
            $signStr = $signStr . "bank_code=" . $bank_code . "&";
        }
        if ($client_ip != "") {
            $signStr = $signStr . "client_ip=" . $client_ip . "&";
        }
        if ($extend_param != "") {
            $signStr = $signStr . "extend_param=" . $extend_param . "&";
        }
        if ($extra_return_param != "") {
            $signStr = $signStr . "extra_return_param=" . $extra_return_param . "&";
        }
        $signStr = $signStr . "input_charset=" . $input_charset . "&";
        $signStr = $signStr . "interface_version=" . $interface_version . "&";
        $signStr = $signStr . "merchant_code=" . $merchant_code . "&";
        $signStr = $signStr . "notify_url=" . $notify_url . "&";
        $signStr = $signStr . "order_amount=" . $order_amount . "&";
        $signStr = $signStr . "order_no=" . $order_no . "&";
        $signStr = $signStr . "order_time=" . $order_time . "&";
        if ($pay_type != "") {
            $signStr = $signStr . "pay_type=" . $pay_type . "&";
        }
        $signStr = $signStr . "product_name=" . $product_name . "&";
        if ($return_url != "") {
            $signStr = $signStr . "return_url=" . $return_url . "&";
        }
        $signStr = $signStr . "service_type=" . $service_type;
        openssl_sign($signStr, $sign_info, $priKey, OPENSSL_ALGO_MD5);
        $sign = base64_encode($sign_info);
        return $sign;
    }

    public function notify() {

        global $merchant_code, $interface_version, $sign_type, $dinpaySign, $notify_type, $notify_id, $order_no, $order_time, $order_amount, $trade_status, $trade_time, $trade_no, $bank_seq_no, $extra_return_param;
        /////////////////////////////////获取智付公钥（在商家后台复制出来）//////////////////////
        $pubKey = openssl_get_publickey(C('bank_public'));
        //////////////////////////		接收智付返回通知数据  /////////////////////////////////

        $merchant_code = $_REQUEST["merchant_code"];
        $interface_version = $_REQUEST["interface_version"];
        $sign_type = $_REQUEST["sign_type"];
        $dinpaySign = base64_decode($_REQUEST["sign"]);
        $notify_type = $_REQUEST["notify_type"];
        $notify_id = $_REQUEST["notify_id"];
        $order_no = $_REQUEST["order_no"];
        $order_time = $_REQUEST["order_time"];
        $order_amount = $_REQUEST["order_amount"];
        $trade_status = $_REQUEST["trade_status"];
        $trade_time = $_REQUEST["trade_time"];
        $trade_no = $_REQUEST["trade_no"];
        $bank_seq_no = $_REQUEST["bank_seq_no"];
        $extra_return_param = $_REQUEST["extra_return_param"];
        if ($bank_seq_no != "") {
            $signStr = $signStr . "bank_seq_no=" . $bank_seq_no . "&";
        }
        if ($extra_return_param != "") {
            $signStr = $signStr . "extra_return_param=" . $extra_return_param . "&";
        }
        $signStr = $signStr . "interface_version=" . $interface_version . "&";
        $signStr = $signStr . "merchant_code=" . $merchant_code . "&";
        $signStr = $signStr . "notify_id=" . $notify_id . "&";
        $signStr = $signStr . "notify_type=" . $notify_type . "&";
        $signStr = $signStr . "order_amount=" . $order_amount . "&";
        $signStr = $signStr . "order_no=" . $order_no . "&";
        $signStr = $signStr . "order_time=" . $order_time . "&";
        $signStr = $signStr . "trade_no=" . $trade_no . "&";
        $signStr = $signStr . "trade_status=" . $trade_status . "&";
        $signStr = $signStr . "trade_time=" . $trade_time;
        $result = openssl_verify($signStr, $dinpaySign, $pubKey, OPENSSL_ALGO_MD5);

        return $result;
    }

    public function query() {
        global $order, $merchant_code, $interface_version, $sign_type, $service_type, $order_no, $trade_no;
        $order = $this->param['order'];
        /////////////////////////////////接收表单提交参数//////////////////////////////////////
        $merchant_code = C('bank_business');
        $interface_version = "V3.0";
        $sign_type = "RSA-S";
        $service_type = "single_trade_query";
        $order_no = $order;
        $trade_no = "";
        $sign = $this->getSign_query();
        ///////取XML信息
        $param = array(
            'interface_version' => $interface_version,
            'service_type' => $service_type,
            'merchant_code' => $merchant_code,
            'sign_type' => $sign_type,
            'sign' => $sign,
            'order_no' => $order_no,
            'trade_no' => $trade_no,
        );
        $xml = HTTP::http_getFile('https://query.dinpay.com/query', $param, $header, "POST");
        return XMLLoader::xml_to_array($xml)['response'];
    }

    public function getSign_query() {
        global $order, $merchant_code, $interface_version, $sign_type, $service_type, $order_no, $trade_no;
        $priKey = openssl_get_privatekey(C('bank_private'));
        /**
          签名规则定义如下：
          （1）参数列表中，除去sign_type、sign两个参数外，其它所有非空的参数都要参与签名，值为空的参数不用参与签名；
          （2）签名顺序按照参数名a到z的顺序排序，若遇到相同首字母，则看第二个字母，以此类推，同时将商家支付密钥key放在最后参与签名，组成规则如下：
          参数名1=参数值1&参数名2=参数值2&……&参数名n=参数值n&key=key值
         */
        $signStr = "interface_version=" . $interface_version . "&merchant_code=" . $merchant_code . "&order_no=" . $order_no . "&service_type=" . $service_type;
        if ($trade_no != "") {
            $signStr = $signStr . "&trade_no=" . $trade_no;
        }
        openssl_sign($signStr, $sign_info, $priKey, OPENSSL_ALGO_MD5);
        $sign = base64_encode($sign_info);
        return $sign;
    }

}
