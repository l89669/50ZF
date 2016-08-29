<?php

namespace Home\Model;

use Think\Model;
use Com\DDMCloud\Common;

class PlayerlogModel extends Model {

    public function log($payorder, $server, $player, $before, $after) {
        $data['payorder'] = $payorder;
        $data['server'] = $server;
        $data['player'] = $player;
        $data['before'] = $before;
        $data['after'] = $after;
        $data['time'] = time();
        $data['ip'] = Common::get_client_ip();
        $result = $this->data($data)->add();
        return $result;
    }

    public function search($where, $limit, $lfrom, $lto, $order) {
        $this->where($where);
        $this->order($order);
        if ($limit) {
            $this->limit("{$limit}," . ($lfrom - $lto));
        }
        return $this->select();
    }

}
