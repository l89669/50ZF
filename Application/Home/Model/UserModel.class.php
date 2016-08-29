<?php

namespace Home\Model;

use Think\Model;

class UserModel extends Model {

    public function existUser($user) {
        $username = $user;
        $result = $this->where(array('username' => $username))->count();
        return ($result > 0);
    }

    public function loadUser($user) {
        $this->startTrans();
        $this->lock(true);
        $username = $user;
        $result = $this->where(array('username' => $username))->limit(1)->select();
        $this->commit();
        if (!count($result)) {
            $this->saveUser($user);
            return $this->loadUser($user);
            //return false;
        }
        return $result[0];
    }

    public function saveUser($user, $params) {
        if (!$user) {
            return;
        }
        $exist = $this->existUser($user);
        $username = $user;
        if ($exist) {//已存在就保存数据
            $result = $this->where(array('username' => $username))->data($params)->save();
            return $result;
        }//不存在直接新建数据
        $regtime = time();
        $today = date('Ymd');
        $result = $this->data(array('username' => $user, 'regtime' => $regtime, 'gain_last_time' => $today))->add();
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
            return false;
        }
        if (is_array($param)) {//单条 多条
            foreach ($param as $key => $value) {
                $tmp[$value] = $u[$value];
            }
            return $tmp;
        }
        return $u[$param];
    }

    public function freezeUser($user, $freeze = true) {
        return $this->saveUser($user, array('frozen' => $freeze));
    }

    public function addMoney($user, $money) {
        $sqlmoney = $this->getParam($user, 'money');
        $money = $money + $sqlmoney;
        return $this->saveUser($user, array('money' => $money));
    }

    public function takeMoney($user, $money) {
        $sqlmoney = $this->getParam($user, 'money');
        $money = $sqlmoney - $money;
        return $this->saveUser($user, array('money' => $money));
    }

    public function getMoney($user) {
        return $this->getParam($user, 'money');
    }

    public function addGain($user, $gain) {
        $sqlmoney = $this->getParam($user, 'gain');
        $gain = $sqlmoney + $gain;
        return $this->saveUser($user, array('gain' => $gain));
    }

    public function takeGain($user, $gain) {
        $sqlmoney = $this->getParam($user, 'gain');
        $gain = $sqlmoney - $gain;
        return $this->saveUser($user, array('gain' => $gain));
    }

    public function getGain($user) {
        return $this->getParam($user, 'gain');
    }

    public function addGain_Last($user, $gain) {
        $sqlmoney = $this->getParam($user, 'gain_last');
        $gain = $sqlmoney + $gain;
        return $this->saveUser($user, array('gain_last' => $gain));
    }

    public function takeGain_Last($user, $gain) {
        $sqlmoney = $this->getParam($user, 'gain_last');
        $gain = $sqlmoney - $gain;
        return $this->saveUser($user, array('gain_last' => $gain));
    }

    public function getGain_Last($user) {
        return $this->getParam($user, 'gain_last');
    }

    /*
      public function updateGain($user) {
      $gain_last_time = $this->getParam($user, 'gain_last_time');
      $today = date('Ymd');
      if ($gain_last_time == $today) {
      return;
      }
      $gain = $this->getGain_Last($user);
      $result = $this->takeGain_Last($user, $gain);
      if (!is_null($result)) {
      $result = $this->addGain($user, $gain);
      if (!$result) {
      $this->addGain_Last($user, $gain);
      return false;
      }
      $this->saveUser($user, array('gain_last_time' => $today));
      return true;
      }

      return false;
      }
     * 
     */

    public function updateGain($user) {
        $gain_last_time = $this->getParam($user, 'gain_last_time');
        $today = date('Ymd');
        if ($gain_last_time == $today) {
            return;
        }
        $this->startTrans();
        $this->lock(true);
        ////////////////////
        $gain = $this->getGain_Last($user); //上次收益
        $this->takeGain_Last($user, $gain); //扣除上次
        $this->addGain($user, $gain); //增加收益
        $this->saveUser($user, array('gain_last_time' => $today)); //保存时间
        $result = $this->commit();
        if ($result) {
            return true;
        }
        $this->rollback();
        return false;
    }

    public function setSafePassword($user, $safepassword) {
        return $this->saveUser($user, array('safepassword' => $safepassword));
    }

    public function getSafePasswrod($user) {
        return $this->getParam($user, 'safepassword');
    }

    public function querySafePassword($user, $safepassword) {
        $tmp = $this->getSafePasswrod($user);
        if ($tmp == $safepassword) {
            return true;
        }
        return false;
    }

    public function gainToMoney($user) {
        $this->startTrans();
        $this->lock(true);
        $userGain = $this->getGain($user);
        $userMoney = $this->getMoney($user);
        $newMoney = $userMoney + $userGain;
        $this->saveUser($user, ['gain' => 0, 'money' => $newMoney]);
        $result = $this->commit();
        if ($result) {
            return $newMoney;
        }
        $this->rollback();
        return false;
    }

}
