<?php

namespace Com\DDMCloud\PayGateway;

use Com\DDMCloud\HTTP;
use Com\DDMCloud\PayGateway\Business;

/**
 * 支付方式
 */
class BackNotify {

    public static function getList() {
        $try = C('BackNotifyTry');
        $m = self::getSQL();
        $usefulTime = (time() - C('BackNotifyValidTime'));
        $bankWhere = "(`success`=true AND `order_status`=1)";
        $cardWhere = "(`order_card_status`<>'')";
        $where = "(`success_notify`=false AND `order_notifyurl`<>'' AND `order_paytime`>$usefulTime AND `order_notify`<{$try}) AND ($bankWhere OR $cardWhere)";
        $result = $m->where($where)->select();
        return $result;
    }

    private static function getSQL() {
        return M('order');
    }

    public static function getSign($order) {
        $key = Business::getKey($order['order_business']);
        //echo self::getSignString($order) . "key=$key";
        return MD5(self::getSignString($order) . $key);
    }

    private static function getSignString($order) {
        $str .="order_business={$order['order_business']}&";
        $str .="order_card_status={$order['order_card_status']}&";
        $str .="order_no={$order['order_no']}&";
        $str .="order_notifyurl={$order['order_notifyurl']}&";
        $str .="order_param={$order['order_param']}&";
        $str .="order_returnurl={$order['order_returnurl']}&";
        $str .="order_status={$order['order_status']}&";
        $str .="pay_amount={$order['pay_amount']}&";
        $str .="pay_type={$order['pay_type']}&";
        $str .="pay_way={$order['pay_way']}&";
        $str .="success={$order['success']}";
        return $str;
    }

    public static function getNotifyParams($order) {
        $params = "?";
        $sign = self::getSign($order);
        foreach ($order as $key => $value) {
            $params .= "$key=$value&";
        }
        $params .= "sign=$sign";

        return $params;
    }

}
