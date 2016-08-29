<?php

namespace Home\Controller;

use Think\Controller;

class ServerpayController extends Controller {

    public function _empty($name) {
        $this->index();
    }

    public function index() {
        $id = I('id');
        $mS = D('servers');
        $mPayway = D('payway');
        $mBanks = D('banks');
        $server = $mS->loadServer($id);
        if (!$server) {
            $this->error(L('web_lang_pay_0'), U('index/index'));
        }
        /*
          $tmp = $mPayway->getPayway(); //支付方式列表
          $banks = $mBanks->getBanks();
          foreach ($tmp as $key => $value) {
          if (!$value['enable']) {
          continue;
          }
          if ($value['way'] == 'bank' && $value['type'] == 'all') {
          foreach ($banks as $bkey => $bvalue) {
          $payway[] = ['way' => 'bank', 'type' => $bvalue['bank'], 'name' => $bvalue['name'], 'pic' => $bvalue['pic']];
          }
          continue;
          }
          $payway[] = $value;
          }
         * 
         */
        $payway = $mPayway->getServerPaySet($id);
        $this->assign([
            'server' => $server,
            'payway' => $payway,
            'rate' => json_encode($mS->getServerRate($server['id']), JSON_UNESCAPED_UNICODE),
        ]);
        $this->display();
    }

}
