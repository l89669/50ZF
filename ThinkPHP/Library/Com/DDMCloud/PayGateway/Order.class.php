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

    private $sql;
    private $param;

    function __construct($param) {
        $this->sql = self::getSql();
        if (!is_array($param)) {
            $this->setOrder($param);
            return;
        }
        if (!$this->param['order']) {
            $this->param['order'] = self::randomOrder();
        }
        //下面只是创建订单用的
        $this->param['order_body'] = $param['body'];
        $this->param['order_business'] = $param['partner'];
        $this->param['order_subject'] = $param['subject'];
        $this->param['pay_amount'] = $param['total_fee'];
        $this->param['sign'] = $param['sign'];
        $this->param['order_no'] = $param['order'];
        $this->param['order_param'] = $param['param'];
        $this->param['pay_way'] = $param['payway'];
        $this->param['pay_type'] = $param['paytype'];
        $this->param['order_returnurl'] = $param['returnurl'];
        $this->param['order_notifyurl'] = $param['notifyurl'];
        return;
    }

    /**
     * 设置订单数据库中信息
     * @param type $order
     * @return \Com\DDMCloud\PayGateway\Order
     */
    public function setOrder($order) {
        $this->param['order'] = $order;
        if (!$order) {
            $this->param['order'] = self::randomOrder();
        }
        $this->updateSqlCache();
        return $this;
    }

    /**
     * 加载数据库记录到对象
     */
    public function loadSqlParam() {
        $m = $this->sql;
        //$result = $m->limit(1)->where("`order` = '{$this->param['order']}'")->select();
        $this->param = $this->getSqlOrder();
        /*
          $this->param['order'] = $result[0]['order'];
          $this->param['order_body'] = $result[0]['order_body'];
          $this->param['order_business'] = $result[0]['order_business'];
          $this->param['order_subject'] = $result[0]['order_subject'];
          $this->param['pay_amount'] = $result[0]['pay_amount'];
          $this->param['order_no'] = $result[0]['order_no'];
          $this->param['order_param'] = $result[0]['order_param'];
          $this->param['pay_way'] = $result[0]['pay_way'];
          $this->param['pay_type'] = $result[0]['pay_type'];
          $this->param['order_returnurl'] = $result[0]['order_returnurl'];
          $this->param['order_notifyurl'] = $result[0]['order_notifyurl'];
          $this->param['order_time'] = $result[0]['order_time'];
          $this->param['order_paytime'] = $result[0]['order_paytime'];
          $this->param['order_status'] = $result[0]['order_status'];
          $this->param['order_freeze'] = $result[0]['order_freeze'];
          $this->param['order_notify'] = $result[0]['order_notify'];
          $this->param['success'] = $result[0]['success'];
          $this->param['success_divide'] = $result[0]['success_divide'];
          $this->param['success_notify'] = $result[0]['success_notify'];
          $this->param['success_return'] = $result[0]['success_return'];
         * 
         */
        return $this;
    }

    public function updateSqlCache() {
        $this->loadSqlParam();
        return $this;
    }

    private static function getSql() {
        return M('order');
    }

    private function randomOrder() {
        $data = date('YmdHis');
        $random = rand(100000, 999999);
        return $data . $random;
    }

    /**
     * 录入系统sign
     */
    public function setSystemSign() {
        return $this->getSign();
    }

    private function getSign() {
        $signStr = "body={$this->param['order_body']}&";
        $signStr .= "partner={$this->param['order_business']}&";
        $signStr .= "order={$this->param['order_no'] }&";
        $signStr .= "notifyurl={$this->param['order_notifyurl']}&";
        $signStr .= "param={$this->param['order_param'] }&";
        $signStr .= "returnurl={$this->param['order_returnurl']}&";
        $signStr .= "subject={$this->param['order_subject']}&";
        $signStr .= "total_fee={$this->param['pay_amount']}&";
        $signStr .= "paytype={$this->param['pay_type']}&";
        $signStr .= "payway={$this->param['pay_way']}";
        $signStr .= Business::getKey($this->param['order_business']);
        return MD5($signStr);
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
        Logs::logDefault('createorder', "Start create order {$this->param['order']} by {$this->param['order_business']}(" . Business::getBusinessName($this->param['order_business']) . ")", $this->param['order_business'], $this->param['order']);
        //开始写入
        $m = $this->sql;
        $data["order"] = $this->param['order'];
        $data["pay_amount"] = $this->param['pay_amount'];
        $data["pay_way"] = $this->param['pay_way'];
        $data["pay_type"] = $this->param['pay_type'];
        $data["order_returnurl"] = $this->param['order_returnurl'];
        $data["order_notifyurl"] = $this->param['order_notifyurl'];
        $data["order_business"] = $this->param['order_business'];
        $data["order_divide"] = Business::getDivide($data["order_business"], $data["pay_way"], $data['pay_type']);
        $data["order_no"] = $this->param['order_no'];
        $data["order_time"] = time();
        $data["order_param"] = $this->param['order_param'];
        $data["order_body"] = $this->param['order_body'];
        $data["order_subject"] = $this->param['order_subject'];
        $m->data($data);
        $temp = $m->add();
        if (!$temp) {
            $result['error'] = 201;
            return $result;
        }
        $result['result']['order'] = $this->getSystemOrder();
        $result['result']['payurl'] = U("payorder/index?order={$this->getSystemOrder()}", NULL, NULL, true);
        $result['error'] = 0;
        Logs::logDefault('createorder', "Order {$this->param['order']} create success", $this->param['order_business'], $this->param['order']);
        return $result;
    }

    /**
     * 检查URL中订单请求
     * @return int
     */
    public function checkURLOrder() {
        if (!$this->param['order_body']) {
            return 101;
        }
        if (!$this->param['order_business']) {
            return 102;
        }
        if (!Business::existBusiness($this->param['order_business'])) {
            return 102001;
        }
        if (!$this->param['order_subject']) {
            return 104;
        }
        if (!$this->param['pay_amount'] || ((float) $this->param['pay_amount']) <= 0) {
            return 105;
        }
        if (!$this->param['sign']) {
            return 106;
        }
        if (!$this->param['order_no']) {
            return 107;
        }
        if ($this->existOrder()) {//订单已存在
            $result['error'] = 108;
            return $result;
        }
        if ($this->getSign() != $this->param['sign']) {
            //调试
           // echo $this->getSign() . "   ";
            return 109;
        }
        if (!$this->param['pay_way']) {
            return 111;
        }
        if (($this->param['pay_way'] == 'bank' || $this->param['pay_way'] == "card") && (!$this->param['pay_type'])) {
            return 111001;
        }
        if (!Payway::existPayway($this->param['pay_way'])) {
            return 111002;
        }
        if (!Payway::existPaytype($this->param['pay_way'], $this->param['pay_type'])) {
            return 111003;
        }
        if ($this->param['pay_amount'] > 100 && !Payway::enableBig($this->param['pay_way'], $this->param['pay_type'])) {
            return 111004;
        }
    }

    /**
     * 订单是否存在
     * @return type
     */
    public function existOrder() {
        $m = $this->sql;
        $result = $m->limit(1)->where("`order` = '{$this->param['order']}'")->count();
        return ($result > 0);
    }

    /**
     * 更新订单
     * @param type $data
     * @return type
     */
    public function updateSqlOrder($data) {
        $m = $this->sql;
        $result = $m->where("`order`='" . $this->param['order'] . "'")->setField($data);
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
        $this->setPaytime();
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

    public function setNotify($notify) {
        return $this->updateSqlOrder(array('order_notify' => $notify));
    }

    public function addNotify() {
        return $this->updateSqlOrder(array('order_notify' => $this->getNotify() + 1));
    }

    public function setPaytime() {
        return $this->updateSqlOrder(array('order_paytime' => time()));
    }

    public function setCardStatus($status) {
        return $this->updateSqlOrder(array('order_card_status' => $status));
    }

    /**
     * 获取订单数据库记录
     * @return type
     */
    public function getSqlOrder() {
        return $this->sql->limit(1)->where("`order` = '{$this->param['order']}'")->select()[0];
    }

    /**
     * 获取系统订单号
     * @return type
     */
    public function getSystemOrder() {
        return $this->param['order'];
    }

    public function getAmount() {
        return $this->param['pay_amount'];
    }

    public function getPayWay() {
        return $this->param['pay_way'];
    }

    public function getPayType() {
        return $this->param['pay_type'];
    }

    public function getReturnUrl() {
        return $this->param['order_returnurl'];
    }

    public function getNotifyUrl() {
        return $this->param['order_notifyurl'];
    }

    public function getBusiness() {
        return $this->param['order_business'];
    }

    public function getDivide() {
        return $this->param['order_divide'];
    }

    public function getOrder() {
        return $this->param['order_no'];
    }

    public function getTime() {
        return $this->param['order_time'];
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

    public function getOther() {
        return $this->param['order_param'];
    }

    public function getBody() {
        return $this->param['order_body'];
    }

    public function getSubject() {
        return $this->param['order_subject'];
    }

    public function getOrderStatus() {
        return $this->param['order_status'];
    }

    public function getNotify() {
        return $this->param['order_notify'];
    }

    public function getPaytime() {
        return $this->param['order_paytime'];
    }

    public function isSuccess() {
        return $this->param['success'];
    }

    public function isSuccess_divide() {
        return $this->param['success_divide'];
    }

    public function isSuccess_notify() {
        return $this->param['success_notify'];
    }

    public function isSuccess_return() {
        return $this->param['success_return'];
    }

    /**
     * 分成
     * @return boolean
     */
    public function divide() {
        $this->sql->startTrans();
        $this->sql->lock(true);
        $order = $this->getSqlOrder();
        if ($order['success_divide']) {
            $this->sql->commit();
            $this->sql->lock(false);
            return true;
        }
        $user = User::getUserFromId($order['order_business']);
        $money = ((float) $order['pay_amount']) * (((float) $order['order_divide']) / 100);
        $user->addAmount($money);
        $this->setSuccess_divide();
        $this->sql->commit();
        $this->sql->lock(false);
        return $money;
    }

    public static function searchByOrderno($business, $no) {
        $m = self::getSql();
        $where['order_no'] = $no;
        $where['order_business'] = $business;
        $result = $m->limit(1)->where($where)->select();
        return $result[0]['order'];
    }

}
