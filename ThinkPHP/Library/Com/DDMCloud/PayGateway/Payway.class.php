<?php

namespace Com\DDMCloud\PayGateway;

use Com\DDMCloud\PayGateway\Payway\Dinpay;
use Com\DDMCloud\PayGateway\Payway\i3ka;
use Com\DDMCloud\PayGateway\Payway\Lcardy;
use Com\DDMCloud\PayGateway\Payway\Yijiapay;
use Com\DDMCloud\PayGateway\Payway\Shanhaipay;
use Com\DDMCloud\PayGateway\Payway\Iwmpay;

/**
 * 支付方式
 */
class Payway {

    private static function getSql() {
        return M('payway');
    }

    public static function existPaytype($payway, $paytype) {
        $m = self::getSql();
        $where = "`payway`='$payway'";
        if ($paytype) {
            $where .= "AND `paytype`='$paytype' AND `open`=true";
        }
        $result = $m->limit(1)->where($where)->count();
        return ($result > 0);
    }

    public static function existPayway($payway) {
        $m = self::getSql();
        $result = $m->limit(1)->where("`payway`='$payway' AND `open`=true")->count();
        return ($result > 0);
    }

    public static function enableBig($payway, $paytype) {
        $m = self::getSql();
        $where = "`payway`='{$payway}'";
        if ($paytype) {
            $where .= " AND `paytype`='{$paytype}' AND `open`=true";
        }
        $result = $m->limit(1)->where($where)->select();
        return $result[0]['enablebig'];
    }

    public static function getClass($name, $way, $type) {
        switch ($name) {
            case 'bank':
                $name = C("bank_{$type}");
                if ($name) {
                    break;
                }
                $name = C('bank_default');
                break;
            case 'card':
                $name = C("card_{$type}");
                if ($name) {
                    break;
                }
                $name = C('card_default');
                break;
            default:
                $name = C("{$name}_default");
                break;
        }
        switch ($name) {
            case 'Dinpay':
                return (new Dinpay());
            case 'i3ka':
                return (new i3ka());
            case 'Lcardy':
                return (new Lcardy());
            case 'Yijiapay':
                return (new Yijiapay());
            case 'Shanhaipay':
                return (new Shanhaipay());
            case 'Iwmpay':
                return (new Iwmpay());
            default:
                return false;
        }
        return false;
    }

}
