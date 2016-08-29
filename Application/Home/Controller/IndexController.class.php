<?php

namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller {

    public function index() {
        $ym = $_SERVER['SERVER_NAME'];
        if ($ym != "mc.50zf.com") {
            //return;
        }
        $do = I('do');
        $view = 'login';
        if ($do) {
            $view = $do;
        }
        $this->theme('default')->display("Index/{$view}");
    }

}
