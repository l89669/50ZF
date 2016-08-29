<?php

namespace Home\Model;

use Think\Model;

class MoneyaccounttypeModel extends Model {

    public function getMoneyAccountTypeList() {
        return $this->select();
    }

    public function existMoneyAccountType($type) {
        $result = $this->where(array('type' => $type))->count();
        return $result > 0;
    }

    public function getMoneyAccountTypeName($type) {
        $name = $this->where(array('type' => $type))->select()[0]['name'];
        if ($name) {
            return $name;
        }
        return $type;
    }

    /*
      public function addMoneyAccountType($type, $name) {

      }

      public function delMoneyAccountType($type) {

      }

      public function editMoneyAccountType($type, $name) {

      }
     * 
     */
}
