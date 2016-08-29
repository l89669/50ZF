<?php

namespace Home\Model;

use Think\Model;
use Com\DDMCloud\Common;

class ApplycashModel extends Model {

    public function getApplyById($id) {
        return $this->where(array('id' => $id))->limit(1)->select();
    }

    public function getApplyByUsername($username, $limit = false, $from, $to) {
        $this->where(array('username' => $username));
        $this->order('id desc');
        if ($limit) {
            $this->limit($from, $to - $from);
        }
        $result = $this->select();
        return $result;
    }

    public function getApply($status, $from, $to) {
        if ($status) {
            $this->where(array('status' => $status));
        }
        return $this->limit($from, $to - $from)->select();
    }

    public function addApply($username, $params) {
        $params['username'] = $username;
        $params['ip'] = Common::get_client_ip();
        $params['time'] = time();
        return $this->data($params)->add();
    }

    public function editApply($id, $params) {
        $this->where(array('id' => $id));
        return $this->data($params)->save();
    }

    public function delApply($id) {
        $this->where(array('id' => $id));
        return $this->delete();
    }

}
