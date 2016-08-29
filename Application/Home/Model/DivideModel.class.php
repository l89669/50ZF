<?php

namespace Home\Model;

use Think\Model;
use Com\DDMCloud\Common;

class DivideModel extends Model {

    public function searchByUsername($username) {
        return $this->where(array('username' => $username))->select();
    }

    public function loadDivide($username, $payway, $paytype) {
        if (!$username || !$payway) {
            return false;
        }
        $this->where(array(array('username' => $username), array('way' => $payway)));
        if ($paytype) {
            $this->where(array(array('type' => $paytype)));
        }
        $tmp = $this->select()[0];
        $tmp = $tmp['divide'];
        if (!$tmp && $paytype) {
            $tmp = $this->where(array(array('username' => $username), array('way' => $payway), array('type' => 'all')))->select()[0];
        }
        if (is_array($tmp)) {
            $tmp = $tmp['divide'];
        }
        return $tmp;
    }

}
