<?php

namespace Com\DDMCloud\PayGateway;

use Com\DDMCloud\PayGateway\Business;

/**
 * 会员操作
 */
class User {

    private $model;
    private $system_sql;
    private $user;
    private $pass;
    private $exist;
    private $amount;
    private $regtime;
    private $regip;
    private $logintime;
    private $freeze;

    function __construct($username) {
        $this->model = M('users');
        $this->user = $username;
        $this->loadSqlUser();
    }

    static public function getUserFromId($id) {
        $m = M('users');
        $result = $m->limit(1)->where("`id` = '{$id}'")->select();
        return (new User($result[0]['user']))->loadSqlUser();
    }

    /**
     * 用户是否存在
     * @return type
     */
    public function existUser() {
        return $this->exist;
    }

    /**
     * 加载数据库记录到对象参数
     */
    private function loadSqlUser() {
        if ($this->system_sql == NULL) {
            $this->loadSqlParam();
        }
        $userinfo = $this->system_sql;
        $this->exist = !($userinfo == NULL); //是否存在
        $this->pass = $userinfo['pass'];
        $this->amount = $userinfo['amount'];
        $this->regtime = $userinfo['regtime'];
        $this->regip = $userinfo['regip'];
        $this->logintime = $userinfo['logintime'];
        $this->freeze = $userinfo['freeze'];
        return $this;
    }

    /**
     * 更新对象缓存
     * @return \Com\DDMCloud\PayGateway\User
     */
    public function updateSqlCache() {
        $this->loadSqlParam();
        $this->loadSqlUser();
        return $this;
    }

    /**
     * 得到数据库记录
     * @return type
     */
    public function getSqlUser() {
        $m = $this->model;
        $result = $m->limit(1)->where("`user` = '{$this->user}'")->select();
        return $result[0];
    }

    /**
     * 登录
     * @param type $pass
     * @return type
     */
    public function login($pass) {//暂时只写密码验证
        $md5 = MD5($pass);
        return ($this->pass == $md5);
    }

    public function register() {
        
    }

    /**
     * 得到分成数据库记录
     * @return type
     */
    public function getBusiness() {
        return Business::getBusiness($this->user);
    }

    /**
     * 得到商户Key
     * @return type
     */
    public function getBusinessKey() {
        return Business::getKey($this->user);
    }

    /**
     * 得到分成比例
     * @param type $type
     * @return type
     */
    public function getBusinessDivide($type) {
        return Business::getDivide($this->user, $type);
    }

    /**
     * 更新数据库记录值
     * @param type $data
     * @return type
     */
    public function updateSqlUser($data) {
        $m = $this->model;
        return $m->where("`user` = '{$this->user}'")->setField($data);
    }

    /**
     * 冻结用户
     * @param type $freeze
     * @return type
     */
    public function freezeSqlUser($freeze = true) {
        $this->freeze = $freeze;
        return $this->updateSqlUser(array('freeze' => $freeze));
    }

    /**
     * 获取余额
     * @return type
     */
    public function getAmount() {
        return $this->amount;
    }

    /**
     * 获取数据库参数
     * @param type $name
     * @return type
     */
    public function getSqlParam($name) {
        if ($this->system_sql == NULL) {
            $this->loadSqlParam();
        }
        return $this->system_sql[$name];
    }

    public function getUser() {
        return $this->user;
    }

    /**
     * 增加余额
     * @param type $add
     * @return type
     */
    public function addAmount($add) {
        $this->model->startTrans();
        $this->model->lock(true);
        $this->updateSqlCache();
        $result = $this->updateSqlUser(array('amount' => ($this->amount + $add)));
        $this->model->commit();
        $this->model->lock(false);
        return $result;
    }

    /**
     * 扣除余额
     * @param type $take
     * @return type
     */
    public function takeAmount($take) {
        $this->model->startTrans();
        $this->model->lock(true);
        $this->updateSqlCache();
        $result = $this->updateSqlUser(array('amount' => ($this->amount - $take)));
        $this->model->commit();
        $this->model->lock(false);
        return $result;
    }

    /**
     * 是否冻结
     * @return type
     */
    public function isFreeze() {
        return $this->freeze;
    }

    /**
     * 加载数据库记录到对象
     * @return type
     */
    public function loadSqlParam() {
        $m = $this->model;
        $result = $m->limit(1)->where("`user` = '{$this->user}'")->select();
        $this->system_sql = $result[0];
        return $this;
    }

    /**
     * 模拟分成
     * @param type $type
     * @param type $amount
     * @return boolean
     */
    public function divide($type, $amount) {
        if (!$type || !$amount) {
            return false;
        }
        $divide = $this->getBusinessDivide($type);
        $money = ($divide / 100) * $amount;
        return $this->addAmount($money);
    }

}
