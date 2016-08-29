<?php

namespace Com\DDMCloud\PayGateway;

use Com\DDMCloud\Common;

/**
 * 订单操作
 */
class Logs {

    /**
     * 统一写记录
     * @param type $type
     * @param type $msg
     * @param type $business
     * @param type $level
     * @return boolean
     */
    private static function log($type, $msg, $business, $level = 'default', $order) {
        if (!$msg) {
            return false;
        }
        $m = self::getSql();
        $data['type'] = $type;
        $data['msg'] = $msg;
        $data['level'] = $level;
        $data['business'] = $business;
        $data['order'] = $order;
        $data['time'] = time();
        $data['ip'] = Common::get_client_ip();
        $result = $m->data($data)->add();
        return $result;
    }

    public static function logPrimary($type, $msg, $business, $order) {
        $level = 'primary';
        return self::log($type, $msg, $business, $level, $order);
    }

    public static function logSuccess($type, $msg, $business, $order) {
        $level = 'success';
        return self::log($type, $msg, $business, $level, $order);
    }

    public static function logInfo($type, $msg, $business, $order) {
        $level = 'info';
        return self::log($type, $msg, $business, $level, $order);
    }

    public static function logWarning($type, $msg, $business, $order) {
        $level = 'warning';
        return self::log($type, $msg, $business, $level, $order);
    }

    public static function logDanger($type, $msg, $business, $order) {
        $level = 'danger';
        return self::log($type, $msg, $business, $level, $order);
    }

    public static function logLink($type, $msg, $business, $order) {
        $level = 'link';
        return self::log($type, $msg, $business, $level, $order);
    }

    public static function logDefault($type, $msg, $business, $order) {
        $level = 'default';
        return self::log($type, $msg, $business, $level, $order);
    }

    /**
     * 统一sql获取
     * @return type
     */
    private static function getSql() {
        return M('logs');
    }

}
