<?php

namespace Home\Model;

use Think\Model;

class UserinfoModel extends Model {

    public function existUser($user) {
        $username = $user;
        $result = $this->where(array('username' => $username))->count();
        return ($result > 0);
    }

    public function loadUser($user) {
        $this->startTrans();
        $this->lock(true);
        $username = $user;
        $result = $this->params = $this->where(array('username' => $username))->limit(1)->select();
        $this->commit();
        if (!count($result)) {
            return false;
        }
        return $result[0];
    }

    public function saveUser($user, $params) {
        $exist = $this->existUser($user);
        $this->startTrans();
        $this->lock(true);
        $username = $user;
        if ($exist) {//已存在就保存数据
            $result = $this->where(array('username' => $username))->data($params)->save();
            $this->commit();
            return $result;
        }//不存在直接新建数据
        $regtime = time();
        $today = date('Ymd');
        $result = $this->data(array('username' => $user, 'regtime' => $regtime, 'gain_last_time' => $today))->add();
        $this->commit();
        if (!$params) {
            return $result;
        }
        return $this->saveUser($user, $params);
    }

    public function getParam($user, $param) {
        if (!$param) {//无需要获取的内容
            return false;
        }
        $u = $this->loadUser($user);
        if (!$u) {//无法取到数据
            $this->saveUser($user);
            return $this->getParam($user, $param);
        }
        if (is_array($param)) {//单条 多条
            foreach ($param as $key => $value) {
                $tmp[$value] = $u[$value];
            }
            return $tmp;
        }
        return $u[$param];
    }

}
