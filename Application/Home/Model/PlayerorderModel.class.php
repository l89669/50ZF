<?php

namespace Home\Model;

use Think\Model;
use Com\DDMCloud\Common;

class PlayerorderModel extends Model {

    public function existPlayerOrder($order) {
        if (!$order) {
            return false;
        }
        return ($this->where([
                    'order' => $order
                ])->count() > 0);
    }

    public function loadPlayerOrder($order) {
        if (!$this->existPlayerOrder($order)) {
            return false;
        }
        return $this->where([
                    'order' => $order
                ])->limit(1)->select()[0];
    }

    public function savePlayerOrder($order, $param) {
        if (!$this->existPlayerOrder($order)) {
            return false;
        }
        $this->startTrans();
        $this->lock(true);
        $this->where([
            'order' => $order
        ]);
        $this->data($param)->save();
        $result = $this->commit();
        return $result;
    }

    public function addPlayerOrder($data) {
        while (!$this->existPlayerOrder($data['order'])) {
            $data['order'] = $this->randomOrder();
            break;
        }
        $data['status'] = '0';
        $data['time'] = time();
        $result = $this->data($data)->add();
        return $data['order'];
    }

    public function payPlayerOrder($order) {
        if (!$this->existPlayerOrder($order)) {
            return false;
        }
        $mP = D('player');
        $order = $this->loadPlayerOrder($order);
        $player = $mP->loadPlayer($order['server'], $order['player']);
        if ($order['status'] > 0) {
            return false;
        }

        if ($player['credit'] < 0) {
            if ((($player['credit'] + $order['credit'])) < $player['credit']) {
                $this->savePlayerOrder($order['order'], ['status' => 2]);
                return false;
            }
        } elseif (($player['credit'] + $order['credit']) < 0) {
            $this->savePlayerOrder($order['order'], ['status' => 2]);
            return false;
        }

        $result = $mP->takeCredit($order['server'], $order['player'], -$order['credit']);
        $mPL = D('playerlog');
        $mPL->log($order['order'], $order['server'], $order['player'], $player['credit'], $player['credit'] + $order['credit']);
        if ($result) {
            $this->savePlayerOrder($order['order'], ['status' => 1]);
            return true;
        } else {
            $this->savePlayerOrder($order['order'], ['status' => 3]);
            return false;
        }
    }

    public function randomOrder() {
        $data = date('YmdHis');
        $random = rand(100000, 999999);
        return $data . $random;
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
