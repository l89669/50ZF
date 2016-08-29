<?php

namespace Com\DDMCloud\PayGateway;

use Com\DDMCloud\PayGateway\User;

/**
 * 订单操作
 */
class Business {

    static public function checkKey($business, $key) {
        $m = M('business');
        $result = $m->limit(1)->where("business='$business' AND key='$key")->select();
        if ($return[0]['key'] = $key) {
            return true;
        }
    }

    static public function getKey($business) {
        $m = M('business');
        $result = $m->limit(1)->where("business='$business'")->select();
        return $result[0]['key'];
    }

    static public function existBusiness($business) {
        $m = M('business');
        $result = $m->limit(1)->where("business='$business'")->count();
        return ($result > 0);
    }

    static public function getBusiness($business) {
        $m = M('business');
        $result = $m->limit(1)->where("`business`='$business'")->select();
        return $result[0];
    }

    static public function getDivide($business, $way, $type) {
        $m = M('business');
        $result = $m->limit(1)->where("`business`='$business'")->select();
        $divide = $result[0]['divide_' . $way];
        if ($type && $way != 'bank') {
            $divide = $result[0]["divide_{$way}_{$type}"];
        }
        if ($divide <= 0 || !$divide) {
            return self::getDefaultDivide($way, $type);
        }
        return $divide;
    }

    static public function getDefaultDivide($way, $type) {
        $m = M('divide');
        $where = "`way`='{$way}'";
        if ($type) {
            $where = "`way`='{$way}' AND `type`='{$type}'";
        }

        $result = $m->limit(1)->where($where)->select();
        $divide = $result[0]['divide'];
        return $divide;
    }

    public static function getBusinessName($business) {
        if (!self::existBusiness($business)) {
            return false;
        }
        $u = User::getUserFromId($business);
        return $u->getUser();
    }

}
