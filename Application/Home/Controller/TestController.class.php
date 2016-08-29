<?php

namespace Home\Controller;

use Think\Controller;
use Com\DDMCloud\UcClient;

class TestController extends Controller {

    public function index() {
        UcClient::init();
    }

    public function tt() {
        $mS = D('payway');
        $result = $mS->getServerPaySet("2");
        dump($result);
    }

}
