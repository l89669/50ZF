<?php

namespace Home\Model;

use Think\Model;
use Com\DDMCloud\Common;

class PlplModel extends Model {

    public function loadData($where) {
        $this->where($where);
        return $this->select();
    }

    public function addData($params) {
        return $this->data($params)->add();
    }

    public function saveData($where, $params) {
        return $this->where($where)->data($params)->save();
    }

}
