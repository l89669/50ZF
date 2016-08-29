<?php

namespace Home\Model;

use Think\Model;

class MoneyaccountModel extends Model {

    public function getMoneyaccount($user) {
        $mt = D('moneyaccounttype');
        $result = $this->where(array('username' => $user))->order('id asc')->select();
        foreach ($result as $key => $value) {
            $type = $value['type'];
            $value['type'] = $mt->getMoneyAccountTypeName($type);
            $result[$key] = $value;
        }
        return $result;
    }

    public function getMoneyaccountById($id) {
        $result = $this->where(array('id' => $id))->limit(1)->select()[0];
        return $result;
    }

    public function addMoneyaccount($user, $type, $realname, $account) {
        $data['username'] = $user;
        $data['type'] = $type;
        $data['realname'] = $realname;
        $data['account'] = $account;
        return $this->data($data)->add();
    }

    public function delMoneyaccount($user, $type, $account) {
        $where['username'] = $user;
        $where['type'] = $type;
        $where['account'] = $account;
        return $this->where($where)->delete();
    }

    public function editMoneyaccount($id, $param) {
        return $this->where(array('id' => $id))->data($param)->save();
    }

    public function searchMoneyaccountById($id) {
        $result = $this->where(array('id' => $id))->limit(1)->select()[0];
        return $result;
    }

}
