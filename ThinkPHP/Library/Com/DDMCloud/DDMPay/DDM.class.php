<?php

namespace Com\DDMCloud\DDMPay;

class DDM {
    /*
      const DDMPAY_ORDER_CREATE = "http://pay.igamer.ren/Createorder/V1/";
      const DDMPAY_ORDER_PAY = "http://pay.igamer.ren/Payorder/index/";
      const DDMPAY_ORDER_QUERY = "http://pay.igamer.ren/Queryorder/V1/";
     */

    const DDMPAY_ORDER_CREATE = "http://pay.ddmcloud.com/Createorder/V1/";
    const DDMPAY_ORDER_PAY = "http://pay.ddmcloud.com/Payorder/index/";
    const DDMPAY_ORDER_QUERY = "http://pay.ddmcloud.com/Queryorder/V1/";

    private $param;
    private $apiId;
    private $apiKey;

    public function __construct($param) {
        $this->param = $param;
    }

    public function app($appId, $appKey) {
        $this->apiId($appId);
        $this->apiKey($appKey);
        return $this;
    }

    public function apiId($apiId) {
        $this->apiId = $apiId;
        $this->param['partner'] = $apiId;
        return $this;
    }

    public function apiKey($apiKey) {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function create() {
        $sign = $this->getCreateSign();
        $res = null;
        foreach ($this->param as $key => $value) {
            $res .= "{$key}=$value&";
        }
        $res .= "sign=$sign";
        $url = self::DDMPAY_ORDER_CREATE . '?' . $res;
        $res = HTTP::request($url);
        return json_decode($res, true);
    }

    public static function goPay($orderId, $client) {
        if (is_array($orderId)) {
            $orderId = $orderId['result']['order'];
        }
        $url = self::DDMPAY_ORDER_PAY . "?order={$orderId}";
        if ($client == "true") {
            $url.="&client=true";
        }
        return $url;
    }

    public static function goPay_Card($orderId, $cardNo, $cardPwd) {
        if (is_array($orderId)) {
            $orderId = $orderId['result']['order'];
        }
        $url = self::DDMPAY_ORDER_PAY . "?order={$orderId}&cardno={$cardNo}&cardpwd={$cardPwd}";
        $res = HTTP::request($url);
        return json_decode($res, true);
    }

    public static function notify($Key) {
        $sign = $_GET['sign'];
        if ($sign == self::getNotifySign($Key)) {
            return true;
        }
        return false;
    }

    private function getCreateSign() {
        $signStr = "body={$this->param['body']}&";
        $signStr .= "partner={$this->apiId}&";
        $signStr .= "order={$this->param['order']}&";
        $signStr .= "notifyurl={$this->param['notifyurl']}&";
        $signStr .= "param={$this->param['param']}&";
        $signStr .= "returnurl={$this->param['returnurl']}&";
        $signStr .= "subject={$this->param['subject']}&";
        $signStr .= "total_fee={$this->param['total_fee']}&";
        $signStr .= "paytype={$this->param['paytype']}&";
        $signStr .= "payway={$this->param['payway']}";
        $signStr .= $this->apiKey;
        return MD5($signStr);
    }

    private static function getNotifySign($key) {
        $order = $_GET;
        $signStr = null;
        $signStr .="order_business={$order['order_business']}&";
        $signStr .="order_card_status={$order['order_card_status']}&";
        $signStr .="order_no={$order['order_no']}&";
        $signStr .="order_notifyurl={$order['order_notifyurl']}&";
        $signStr .="order_param={$order['order_param']}&";
        $signStr .="order_returnurl={$order['order_returnurl']}&";
        $signStr .="order_status={$order['order_status']}&";
        $signStr .="pay_amount={$order['pay_amount']}&";
        $signStr .="pay_type={$order['pay_type']}&";
        $signStr .="pay_way={$order['pay_way']}&";
        $signStr .="success={$order['success']}";
        $signStr .= $key;
        return MD5($signStr);
    }

    /*
      public static function queryOrder($order){

      }
     * 
     */
}

/**
 * HTTP
 */
class HTTP {

    function __construct() {
        
    }

    /**
     * 最简单的XML转数组
     * @param string $xmlstring XML字符串
     * @return array XML数组
     */
    public function xml_to_array($xmlstring) {
        return json_decode(json_encode((array) simplexml_load_string($xmlstring)), true);
    }

    /**
     * http请求
     * @param type $parameter 参数
     * @param type $header 头
     * @param type $method 类型
     */
    public static function http_getFile($url, $params, $header, $method = "GET", $needHeader = false) {
        return self::request($url, $params, $method, $header, $needHeader, false);
    }

    /**
     * 发起一个HTTP/HTTPS的请求
     * @param $url 接口的URL 
     * @param $params 接口参数   array('content'=>'test', 'format'=>'json');
     * @param $method 请求类型    GET|POST
     * @param $multi 图片信息
     * @param $extheaders 扩展的包头信息
     * @return string
     */
    public static function request($url, $params = array(), $method = 'GET', $extheaders = array(), $needHeader = false, $multi = false) {
        if (!function_exists('curl_init'))
            exit('Need to open the curl extension');
        $method = strtoupper($method);
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_USERAGENT, 'PHP-SDK OAuth2.0');
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ci, CURLOPT_TIMEOUT, 3);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ci, CURLOPT_HEADER, $needHeader);
        $headers = (array) $extheaders;
        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($params)) {
                    if ($multi) {
                        foreach ($multi as $key => $file) {
                            $params[$key] = '@' . $file;
                        }
                        curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
                        $headers[] = 'Expect: ';
                    } else {
                        curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($params));
                    }
                }
                break;
            case 'DELETE':
            case 'GET':
                $method == 'DELETE' && curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($params)) {
                    $url = $url . (strpos($url, '?') ? '&' : '?')
                            . (is_array($params) ? http_build_query($params) : $params);
                }
                break;
        }
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE);
        curl_setopt($ci, CURLOPT_URL, $url);
        if ($headers) {
            curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ci);
        curl_close($ci);
        return $response;
    }

}
