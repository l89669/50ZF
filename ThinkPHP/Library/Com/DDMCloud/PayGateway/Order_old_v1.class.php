<?php

namespace Com\DDMCloud\PayGateway;

use Com\DDMCloud\PayGateway\Business;
use Com\DDMCloud\PayGateway\User;
use Com\DDMCloud\PayGateway\Payway;
use Com\DDMCloud\PayGateway\Logs;

/**
 * 订单操作
 */

class Order {

    private $model;
    private $system_sql;
    private $system_orderid;
    private $system_sign;
    private $freeze;
    private $success;
    private $success_divive;
    private $success_notify;
    private $success_return;
    private $sign;      //校验字符
    private $param;     //参数组
    private $body;      //订单信息
    private $notifyurl; //notify路径
    private $order;     //商户订单号
    private $other;     //其他附加信息
    private $partner;   //订单归属商户号
    private $paytype;   //支付子方式 网银，点卡
    private $payway;    //支付方式
    private $returnurl; //return路径
    private $subject;   //订单简介
    private $total_fee; //订单金额

    function __construct($param) {
        $this->model = M('order');
        if (!is_array($param)) {
            $this->setOrder($param);
            $this->updateSqlCache();
            return;
        }
        $this->param = $param;
        if (!$this->system_orderid) {
            $this->system_orderid = self::randomOrder();
        }
        $this->body = $param['body'];
        $this->partner = $param['partner'];
        $this->subject = $param['subject'];
        $this->total_fee = $param['total_fee'];
        $this->sign = $param['sign'];
        $this->order = $param['order'];
        $this->other = $param['param'];
        $this->payway = $param['payway'];
        $this->paytype = $param['paytype'];
        $this->returnurl = $param['returnurl'];
        $this->notifyurl = $param['notifyurl'];
        return;
    }

    private function randomOrder() {
        $data = date('YmdHis');
        $random = rand(100000, 999999);
        return $data . $random;
    }

    /**
     * 设置订单数据库中信息
     * @param type $order
     * @return \Com\DDMCloud\PayGateway\Order
     */
    public function setOrder($order) {
        $this->system_orderid = $order;
        if (!$order) {
            $this->system_orderid = self::randomOrder();
        }
        $this->updateSqlCache();
        return $this;
    }

    /**
     * 获取订单号
     * @return type
     */
    public function getOrder() {
        return $this->system_orderid;
    }

    /**
     * 创建订单
     * @return int
     */
    public function createSqlOrder() {
        $result['error'] = $this->checkURLOrder();
        if ($result['error'] > 0) {//获取传入数据错误
            $result['msg'] = L("create_error_{$result['error']}");
            return $result;
        }
        Logs::logDefault('createorder', "Start create order {$this->system_orderid} by {$this->partner}(" . Business::getBusinessName($this->partner) . ")", $this->partner, $this->system_orderid);
        //开始写入
        $m = $this->model;
        $data["order"] = $this->system_orderid;
        $data["pay_amount"] = $this->total_fee;
        $data["pay_way"] = $this->payway;
        $data["pay_type"] = $this->paytype;
        $data["order_returnurl"] = $this->returnurl;
        $data["order_notifyurl"] = $this->notifyurl;
        $data["order_business"] = $this->partner;
        $data["order_divide"] = Business::getDivide($this->partner, $this->payway);
        $data["order_no"] = $this->order;
        $data["order_time"] = time();
        $data["order_param"] = $this->other;
        $data["order_body"] = $this->body;
        $data["order_subject"] = $this->subject;
        $m->data($data);
        $temp = $m->add();
        if (!$temp) {
            $result['error'] = 201;
            return $result;
        }
        $result['result']['order'] = $this->getOrder();
        $result['result']['payurl'] = U("payorder/index?order={$this->getOrder()}", NULL, NULL, true);
        $result['error'] = 0;
        Logs::logDefault('createorder', "Order {$this->system_orderid} create success", $this->partner, $this->system_orderid);
        return $result;
    }

    /**
     * 检查URL中订单请求
     * @return int
     */
    public function checkURLOrder() {
        if (!$this->param['body']) {
            return 101;
        }
        if (!$this->param['partner']) {
            return 102;
        }
        if (!Business::existBusiness($this->partner)) {
            return 102001;
        }
        if (!$this->param['subject']) {
            return 104;
        }
        if (!$this->param['total_fee'] || ((float) $this->param['total_fee']) <= 0) {
            return 105;
        }
        if (!$this->param['sign']) {
            return 106;
        }
        if (!$this->param['order']) {
            return 107;
        }
        if ($this->existOrder()) {//订单已存在
            $result['error'] = 108;
            return $result;
        }
        $this->setSystemSign(); //写入正确sign
        if ($this->sign != $this->system_sign) {
            //调试
            echo $this->system_sign . "   ";
            return 109;
        }
        if (!$this->param['payway']) {
            return 111;
        }
        if (($this->param['payway'] == 'bank' || $this->param['payway'] == "card") && (!$this->param['paytype'])) {
            return 111001;
        }
        if (!Payway::existPayway($this->param['payway'])) {
            return 111002;
        }
        if (!Payway::existPaytype($this->param['payway'], $this->param['paytype'])) {
            return 111003;
        }
    }

    /**
     * 加载数据库记录到对象
     */
    public function loadSqlOrder() {
        if ($this->system_sql == NULL) {
            $this->loadSqlParam();
        }
        $sql = $this->system_sql;
        $this->body = $sql['order_body'];
        $this->partner = $sql['order_business'];
        $this->subject = $sql['order_subject'];
        $this->total_fee = $sql['pay_amount'];
        $this->order = $sql['order'];
        $this->other = $sql['order_param'];
        $this->payway = $sql['pay_way'];
        $this->paytype = $sql['pay_type'];
        $this->freeze = $sql['order_freeze'];
        $this->success = $sql['success'];
        $this->success_divive = $sql['success_divive'];
        $this->success_notify = $sql['success_notify'];
        $this->success_return = $sql['success_return'];
        $this->notifyurl = $sql['order_notifyurl'];
        $this->returnurl = $sql['order_returnurl'];
        return $this;
    }

    /**
     * 删除订单
     * @return type
     */
    public function deleteSqlOrder() {
        $m = $this->model;
        return $m->where("`order`='" . $this->system_orderid . "'")->delete();
    }

    /**
     * 更新订单
     * @param type $data
     * @return type
     */
    public function updateSqlOrder($data) {
        $m = $this->model;
        return $m->where("`order`='" . $this->system_orderid . "'")->setField($data);
    }

    /**
     * 冻结订单
     * @param type $freeze
     * @return type
     */
    public function freezeSqlOrder($freeze = true) {
        return $this->updateSqlOrder(array('order_freeze' => $freeze));
    }

    /**
     * 录入系统sign
     */
    public function setSystemSign() {
        $signStr = $this->body . $this->notifyurl . $this->order . $this->other . $this->partner . $this->paytype . $this->payway . $this->returnurl . $this->subject . $this->total_fee;
        $this->system_sign = MD5($signStr . Business::getKey($this->partner));
        return $this;
    }

    /**
     * 设置订单状态
     * @param type $status
     * @return type
     */
    public function setSqlStatus($status) {
        return $this->updateSqlOrder(array('order_status' => $status));
    }

    /**
     * 设置订单分成
     * @param type $divide
     * @return type
     */
    public function setSqlDivide($divide) {
        return $this->updateSqlOrder(array('order_divide' => $divide));
    }

    /**
     * 设置支付方式
     * @param type $payway
     * @param type $changeDivide
     * @return type
     */
    public function setSqlPayWay($payway, $changeDivide = true) {
        $result = $this->updateSqlOrder(array('pay_way' => $payway));
        if ($changeDivide) {
            $this->setSqlDefaultDivide();
        }
        return $result;
    }

    /**
     * 重置默认分成
     * @return type
     */
    public function setSqlDefaultDivide() {
        return $this->updateSqlOrder(array('order_divide' => Business::getDivide($this->getSqlParam('order_business'), $this->getSqlParam('pay_way'))));
    }

    /**
     * 设置支付成功
     */
    public function setSuccess() {
        $this->updateSqlOrder(array('order_status' => 1));
        return $this->updateSqlOrder(array('success' => true));
    }

    /**
     * 设置分成成功
     * @return type
     */
    public function setSuccess_divide() {
        return $this->updateSqlOrder(array('success_divide' => true));
    }

    /**
     * 设置notify成功
     * @return type
     */
    public function setSuccess_notify() {
        return $this->updateSqlOrder(array('success_notify' => true));
    }

    /**
     * 设置return成功
     * @return type
     */
    public function setSuccess_return() {
        return $this->updateSqlOrder(array('success_return' => true));
    }

    /**
     * 获取订单数据库记录
     * @return type
     */
    public function getSqlOrder() {
        if ($this->system_sql == NULL) {
            $this->loadSqlParam();
        }
        return $this->system_sql;
    }

    /**
     * 订单是否存在
     * @return type
     */
    public function existOrder() {
        $m = $this->model;
        $result = $m->limit(1)->where("`order` = '{$this->system_orderid}'")->count();
        return ($result > 0);
    }

    /**
     * 检查已写入对象的数据
     * @return int
     */
    public function checkData() {
        if ($this->total_fee <= 0) {
            //return 110;
        }
    }

    /**
     * 分成
     * @return boolean
     */
    public function divide() {
        $this->model->startTrans();
        $this->model->lock(true);
        $order = $this->getSqlOrder();
        if ($order['success_divide']) {
            $this->model->commit();
            $this->model->lock(false);
            return true;
        }
        $user = User::getUserFromId($order['order_business']);
        $money = ((float) $order['pay_amount']) * (((float) $order['order_divide']) / 100);
        $user->addAmount($money);
        $this->setSuccess_divide();
        $this->model->commit();
        $this->model->lock(false);
        return $money;
    }

    /**
     * 加载数据库记录到对象
     */
    public function loadSqlParam() {
        $m = $this->model;
        $result = $m->limit(1)->where("`order` = '{$this->system_orderid}'")->select();
        $this->system_sql = $result[0];
        return $this;
    }

    public function updateSqlCache() {
        $this->loadSqlParam();
        $this->loadSqlOrder();
        return $this;
    }

    public function getPayWay() {
        return $this->payway;
    }

    public function getPayType() {
        return $this->paytype;
    }

    public function getAmount() {
        return $this->total_fee;
    }

    /**
     * 获取订单状态
     */
    public function getOrderStatus() {
        $this->getSqlParam('order_status');
    }

    /**
     * 获取数据库记录
     * @param type $name
     * @return type
     */
    public function getSqlParam($name) {
        if ($this->system_sql == NULL) {
            $this->loadSqlParam();
        }
        return $this->system_sql[$name];
    }

    public function isSuccess() {
        return $this->success;
    }

    public function isSuccess_divide() {
        return $this->success_divive;
    }

    public function isSuccess_notify() {
        return $this->success_notify;
    }

    public function isSuccess_return() {
        return $this->success_return;
    }

    public function getNotifyUrl() {
        return $this->notifyurl;
    }

    public function getReturnUrl() {
        return $this->returnurl;
    }

    public function getSystemSign() {
        return $this->system_sign;
    }

    public function getReturnSign() {
        return MD5($this->system_sign . Business::getKey($this->partner));
    }

}
