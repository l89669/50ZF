<?php

namespace Home\Model;

use Think\Model;

class ServersModel extends Model {

    public function existServer($server) {
        $result = $this->where(array('id' => $server))->count();
        return ($result > 0);
    }

    public function loadServer($server) {
        $result = $this->where(array('id' => $server))->limit(1)->select()[0];
        return $result;
    }

    public function delServer($server) {
        return $this->where(array('id' => $server))->delete();
    }

    public function addServer($params) {
        $params['time'] = time();
        $result = $this->data($params)->add();
        return $result;
    }

    public function saveServer($server, $param) {
        $this->startTrans();
        $this->lock(true);
        $this->where(array('id' => $server))->data($param)->save();
        $result = $this->commit();
        return $result;
    }

    public function searchServerByUsername($username, $limit, $from, $to) {
        $this->where(array('username' => $username))->order('id desc');
        if ($limit) {
            $this->limit($from, $to - $from);
        }
        $result = $this->select();
        return $result;
    }

    public function searchServerByName($name, $limit, $from, $to) {
        $this->where(array('name' => array('like', $name)))->order('id desc');
        if ($limit) {
            $this->limit($from, $to - $from);
        }
        $result = $this->select();
        return $result;
    }

    public function getServerPaySet($username, $id) {
        $mpw = D('payway');
        $ms = D('servers');
        $mBanks = D('banks');
        $banks = $mBanks->getBanks();
        $s = $ms->loadServer($id);
        if ($s['username'] != $username) {
            return;
        }
        $rate = $s['rate'];
        $rate = json_decode($rate, true);
        $pw = $mpw->getPayway();
        foreach ($pw as $key => $value) {
            if ($value['enable'] == false) {
                unset($pw[$key]);
                continue;
            }
            $value['divide'] = $mpw->getdivide($username, $value['way'], $value['type']);
            if (!$value['type']) {//没有子类
                $value['rate'] = $rate["{$value['way']}_all"];
            } else {
                $value['rate'] = $rate["{$value['way']}_{$value['type']}"];
            }
            if (!$value['rate'] && $value['rate'] != 0) {
                $value['rate'] = '100';
            }
            $pw[$key] = $value;
        }
        return $pw;
    }

    public function getServerRate($id) {
        $mPW = D('payway');
        $paySet = $mPW->getServerPaySet($id);
        foreach ($paySet as $key => $value) {
            $payway["{$value['way']}_{$value['type']}"] = $value['rate'];
        }
        return $payway;
        /*
          $mBanks = D('banks');
          $banks = $mBanks->getBanks();
          $server = $this->loadServer($id);
          $paySet = $this->getServerPaySet($server['username'], $id);
          foreach ($paySet as $key => $value) {
          $payway["{$value['way']}_{$value['type']}"] = $value['rate'];
          if ($value['way'] == 'bank' && $value['type'] == 'all') {
          foreach ($banks as $bkey => $bvalue) {
          $payway["bank_{$bvalue['bank']}"] = $value['rate'];
          }
          continue;
          }
          }
          return $payway;
         * 
         */
    }

    public function countGain($server, $from, $to) {
        if (strlen($from) != 10 || !is_numeric($from)) {//非时间戳则转换
            $from = strtotime($from);
        }
        if (strlen($to) != 10 || !is_numeric($to)) {
            $to = strtotime($to);
        }
        if ($to < $from) {//如果时间顺序错误直接交换
            $to += $from;
            $from = $to - $from;
            $to -= $from;
        }
        $mO = D('order');
        $orders = $mO->cache(true)->searchOrderByServer($server, false, null, null, "`status`='1' AND (`paytime` >= '{$from}' AND `paytime`<='{$to}')");
        $money = 0;
        foreach ($orders as $order) {
            $money += (float) $order['money'];
        }
        return $money;
    }

}
