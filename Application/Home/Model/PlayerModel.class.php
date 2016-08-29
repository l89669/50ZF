<?php

namespace Home\Model;

use Think\Model;
use Com\DDMCloud\Common;

class PlayerModel extends Model {

    public function existPlayer($server, $player) {
        return ($this->where([
                    'server' => $server,
                    'player' => $player,
                ])->count() > 0);
    }

    public function loadPlayer($server, $player) {
        if (!$this->existPlayer($server, $player)) {
            $this->createPlayer($server, $player);
        }
        return $this->where([
                    'server' => $server,
                    'player' => $player,
                ])->limit(1)->select()[0];
    }

    public function savePlayer($server, $player, $param) {
        if (!$this->existPlayer($server, $player)) {
            $this->createPlayer($server, $player);
        }
        $this->startTrans();
        $this->lock(true);
        $this->where([
            'server' => $server,
            'player' => $player,
        ]);
        $this->data($param)->save();
        $result = $this->commit();
        return $result;
    }

    public function createPlayer($server, $player) {
        if (!$server || !$player) {
            return;
        }
        $data['server'] = $server;
        $data['player'] = $player;
        $data['regtime'] = time();
        $result = $this->data($data)->add();
        return $result;
    }

    public function searchPlayerByServer($id, $limit, $from, $to, $where, $order) {
        $this->where($where);
        $this->where(array('server' => $id))->order('id desc');
        $this->order($order);
        if ($limit) {
            $this->limit($from, $to - $from);
        }
        $result = $this->select();
        return $result;
    }

    public function searchPlayerByServers($id, $limit, $from, $to, $where) {
        $needCount = abs($to - $from);
        if (!$limit) {
            $needCount = 999999;
        }
        foreach ($id as $sid) {
            if ($limit) {
                $tmp = $this->searchPlayerByServer($sid, $limit, $from, $to - count($player), $where);
            } else {
                $tmp = $this->searchPlayerByServer($sid, $limit, $from, $to, $where);
            }
            foreach ($tmp as $key => $value) {
                $player[] = $value;
            }
            if (count($player) >= $needCount) {
                return $player;
            }
        }
        return $player;
    }

    public function addMoney($server, $player, $money) {
        $p = $this->loadPlayer($server, $player);
        return $this->savePlayer($server, $player, ['money' => $p['money'] + $money]);
    }

    public function getMoney($server, $player) {
        return $this->loadPlayer($server, $player)['money'];
    }

    public function addCredit($server, $player, $credit) {
        $p = $this->loadPlayer($server, $player);
        return $this->savePlayer($server, $player, ['credit' => $p['credit'] + $credit]);
    }

    public function getCredit($server, $player) {
        return $this->loadPlayer($server, $player)['credit'];
    }

    public function takeCredit($server, $player, $credit) {
        $p = $this->loadPlayer($server, $player);
        return $this->savePlayer($server, $player, ['credit' => $p['credit'] - $credit]);
    }

}
