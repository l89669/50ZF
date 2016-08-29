<?php

namespace Home\Model;

use Think\Model;

class OrderModel extends Model {

    public function existOrder($order) {
        $result = $this->where(array('order' => $order))->count();
        return ($result > 0);
    }

    public function loadOrder($order) {
        return $this->where(array('order' => $order))->limit(1)->select()[0];
    }

    public function delOrder($order) {
        return $this->where(array('order' => $order))->delete();
    }

    public function addOrder($params) {
        $order = $this->randomOrder();
        if ($this->existOrder($order)) {
            return false;
        }
        if (!$params['server']) {
            $params['server'] = 'system';
        }
        $params['order'] = $this->randomOrder();
        if (!isset($params['status'])) {
            $params['status'] = 0;
        }
        $params['time'] = time();
        $this->data($params)->add();
        return $params['order'];
    }

    public function saveOrder($order, $param) {
        $this->startTrans();
        $this->lock(true);
        $this->where(array('order' => $order))->data($param)->save();
        $result = $this->commit();
        return $result;
    }

    public function randomOrder() {
        $data = date('YmdHis');
        $random = rand(100000, 999999);
        return $data . $random;
    }

    public function searchOrder($limit, $limitCount, $timefrom, $timeto, $where, $order) {
        if (strlen($timefrom) != 10 || !is_numeric($timefrom)) {//非时间戳则转换
            $timefrom = strtotime($timefrom);
        }
        if (strlen($limitto) != 10 || !is_numeric($limitto)) {
            $limitto = strtotime($limitto);
        }
        if ($limitto < $timefrom) {//如果时间顺序错误直接交换
            $limitto += $timefrom;
            $timefrom = $limitto - $timefrom;
            $limitto -= $timefrom;
        }
        $timeWhere = "`paytime` >= '{$timefrom}' AND `paytime`<='{$timeto}'";
        $this->where($where)->where($timeWhere);
        if ($order) {
            $this->order($order);
        }
        if ($limit) {
            $this->limit($limitCount);
        }
        $orders = $this->select();
        return $orders;
    }

    public function searchOrderByUsername($username, $limit, $from, $to, $where) {
        $this->where(array('username' => $username))->order('id desc');
        $this->where($where);
        if ($limit) {
            $this->limit($from, $to - $from);
        }
        $result = $this->select();
        return $result;
    }

    public function searchOrderByServer($server, $limit, $from, $to, $where) {
        $this->where(array('server' => $server))->order('id desc');
        $this->where($where);
        if ($limit) {
            $this->limit($from, $to - $from);
        }
        $result = $this->select();
        return $result;
    }

    public function reviewStatus($status) {
        switch ($status) {
            case '0';
                return '未付款';
                break;
            case '1':
                return '交易完成';
                break;
            case '2':
                return '处理中[超过五分钟则为处理失败,请自行检查,部分卡密面值可能不支持]';
                break;
            default:
                return '未知';
                break;
        }
    }

    public function reviewRecord($record) {
        foreach ($record as $key => $value) {
            $record[$key]['status'] = $this->reviewStatus($value['status']);
        }
        return $record;
    }

    public function reviewPaydata($paydata) {
        $mPayway = D('payway');
        $paydata['payname'] = $mPayway->loadPayType($paydata['payway'], $paydata['paytype'])['name'];
        return $paydata;
    }

}
