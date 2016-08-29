<?php

namespace Home\Model;

use Think\Model;
use Com\DDMCloud\Common;

class LogModel extends Model {

    private function log($type, $msg, $level = 'default', $order) {
        if (!$msg) {
            return false;
        }
        $data['type'] = $type;
        $data['log'] = $msg;
        $data['level'] = $level;
        $data['order'] = $order;
        $data['time'] = time();
        $data['ip'] = Common::get_client_ip();
        $result = $this->data($data)->add();
        return $result;
    }

    public function logPrimary($type, $msg, $order) {
        $level = 'primary';
        return $this->log($type, $msg, $level, $order);
    }

    public function logSuccess($type, $msg, $order) {
        $level = 'success';
        return $this->log($type, $msg, $level, $order);
    }

    public function logInfo($type, $msg, $order) {
        $level = 'info';
        return $this->log($type, $msg, $level, $order);
    }

    public function logWarning($type, $msg, $order) {
        $level = 'warning';
        return $this->log($type, $msg, $level, $order);
    }

    public function logDanger($type, $msg, $order) {
        $level = 'danger';
        return $this->log($type, $msg, $level, $order);
    }

    public function logLink($type, $msg, $order) {
        $level = 'link';
        return $this->log($type, $msg, $level, $order);
    }

    public function logDefault($type, $msg, $order) {
        $level = 'default';
        return $this->log($type, $msg, $level, $order);
    }

}
