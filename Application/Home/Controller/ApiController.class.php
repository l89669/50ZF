<?php

namespace Home\Controller;

use Think\Controller;
use Com\DDMCloud\DDMPay\DDM;
use Com\DDMCloud\XML;

class ApiController extends Controller {

    private $mL;

    public function __construct() {
        $this->mL = D('log');
        global $apiresult;
        parent::__construct();
        $apiresult['api']['version'] = '1';
        $apiresult['api']['apido'] = ACTION_NAME;
        $apiresult['api']['status'] = C('api_status');
        $apiresult['error'] = '0';
        $this->checkStatus(); //检查接口状态
        if ($apiresult['error'] == 0) {
            return;
        }
        exit;
    }

    public function __destruct() {
        global $apiresult;
        parent::__destruct();
        if ($apiresult['error'] > 0 && !$apiresult['reason']) {
            $apiresult['reason'] = L("api_lang_error_{$apiresult['error']}");
            if ($apiresult['reason'] == "API_LANG_ERROR_{$apiresult['error']}") {
                $apiresult['reason'] = L('api_lang_error_0');
            }
        } elseif (!$apiresult['reason']) {
            $apiresult['reason'] = "操作成功";
        }
        switch (strtolower(I('format'))) {
            case 'json':
                $apiresult = json_encode($apiresult, JSON_UNESCAPED_UNICODE);
                break;
            case 'xml':
                header("Content-type:text/xml");
                $xml = new XML();
                $apiresult = XML::array2XML($apiresult);
                break;
            default:
                $apiresult = json_encode($apiresult, JSON_UNESCAPED_UNICODE);
                break;
        }
        if (ACTION_NAME != "s_player") {
            $this->mL->logDefault('api_' . ACTION_NAME, json_encode(['params' => I('param.'), 'result' => $apiresult], JSON_UNESCAPED_UNICODE));
        }
        echo $apiresult;
    }

    private function setError($error, $reason) {
        global $apiresult;
        $apiresult['error'] = $error;
        if ($reason) {
            $apiresult['reason'] = $reason;
        }
        exit;
    }

    private function setResult($result) {
        global $apiresult;
        $apiresult['result'] = $result;
        exit;
    }

    private function checkParams($params) {
        global $apiresult;
        $params = str_replace('，', ',', $params);
        $params = split(',', $params);
        foreach ($params as $param) {
            if (!I($param)) {
                $this->setError('101', "参数 {$param} 缺少.");
                exit;
            }
        }
    }

    private function checkStatus() {
        global $apiresult;
        if (C('api_status')) {//API通道关闭
            return;
        }
        $this->setError('100');
    }

    private function checkServer() {
        global $apiresult;
        $mS = D('servers');
        $sid = I('id');
        $key = I('key');
        $server = $mS->loadServer($sid);
        if (is_null($server)) {
            $this->setError('201'); //服务器不存在
        }
        if ($server['key'] != $key) {
            $this->setError('202'); //密钥错误
        }
    }

    public function status() {
        global $apiresult;
        $apiresult['result']['status'] = C('api_status');
    }

    public function s_check() {
        $this->checkParams('id,key');
        $this->checkServer();
        global $apiresult;
        $mS = D('servers');
        $sid = I('id');
        $server = $mS->field('id,name,ip,info,logourl,wwwurl,url,rate,time,money')->loadServer($sid);
        $this->setResult(['server' => $server]);
    }

    public function s_player() {
        $this->checkParams('id,key,player');
        $this->checkServer();
        global $apiresult;
        $mP = D('player');
        $sid = I('id');
        $playerName = I('player');
        $playerNames = split(",", $playerName);
        if (count($playerNames) > 100) {
            $this->setError(301, $reason);
        }
        if (count($playerNames) <= 1) {
            $players = $mP->loadPlayer($sid, $playerName);
        } else {
            foreach ($playerNames as $playerName) {
                $players[] = $mP->loadPlayer($sid, $playerName);
            }
        }
        $this->setResult(['player' => $players]);
    }

    public function s_orders() {
        $this->checkParams('id,key');
        $this->checkServer();
        global $apiresult;
        $mO = D('order');
        $sid = I('id');
        $timeFrom = I('timefrom', time() - 36000);
        $timeTo = I('timeto', time());
        $limit = I('limit', false);
        $limitCount = I('limitcount', 10);
        if (!$limit) {
            $limitCount = 10;
        }
        if ($limitCount > 100) {
            $limitCount = 100;
        }
        $orders = $mO->searchOrder(true, $limitCount, $timeFrom, $timeTo, ['server' => $sid], "id desc");
        $this->setResult(['orders' => $orders]);
    }

    public function s_order() {
        $this->checkParams("id,key,order");
        $this->checkServer();
        global $apiresult;
        $mO = D('order');
        $order = I('order');
        $order = $mO->loadOrder($order);
        if ($order) {
            $this->setResult(['order' => $order]);
        }
        $this->setError(203, $reason);
    }

    public function p_payorder() {
        $this->checkParams('id,key,player,credit');
        $this->checkServer();
        global $apiresult;
        $mS = D('servers');
        $mPO = D('playerorder');
        $mP = D('player');
        $sid = I('id');
        $player = I('player');
        $credit = I('credit', 0);
        $info = I('info', '未知');
        $server = $mS->loadServer($sid);
        $playerOrderId = $mPO->addPlayerOrder(['server' => $server['id'], 'player' => $player, 'credit' => -$credit, 'info' => $info]); //创建订单
        $mPO->payPlayerOrder($playerOrderId); //支付订单
        $playerOrder = $mPO->loadPlayerOrder($playerOrderId); //加载订单
        $player = $mP->loadPlayer($server['id'], $player); //获取玩家信息
        $this->setResult(['playerorder' => $playerOrder, 'player' => $player]);
    }

}
