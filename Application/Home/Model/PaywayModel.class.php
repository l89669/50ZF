<?php

namespace Home\Model;

use Think\Model;
use Com\DDMCloud\Common;

class PaywayModel extends Model {

    public function getPayway() {
        $this->cache(true);
        $payway = $this->order('id asc')->select();
        return $payway;
    }

    public function getServerPayway($id) {
        $mS = D('servers');
        $server = $mS->loadServer($id);
        if (!$server) {
            return;
        }
        $pw = $this->where(['enable' => 1])->getPayway();
        $rate = $server['rate'];
        $rate = json_decode($rate, true);
        foreach ($pw as $key => $value) {
            $value['rate'] = $rate["{$value['way']}_{$value['type']}"];
            $value['divide'] = $this->getdivide($server['username'], $value['way'], $value['type']);
            if (!$value['rate'] && $value['rate'] != "0") {
                $value['rate'] = "100";
            }
            $pw[$key] = $value;
        }
        return $pw;
    }

    public function getServerPaySet($id) {
        $mB = D('banks');
        $banks = $mB->getBanks();
        $payway = $this->getServerPayway($id);
        foreach ($payway as $key => $value) {
            if ($value['rate'] == 0) {
                unset($payway[$key]);
                continue;
            }
            if ($value['way'] != "bank" || $value['type'] != "all") {
                continue;
            }
            foreach ($banks as $bKey => $bValue) {
                $payway[] = [
                    'id' => $value['id'],
                    'name' => $bValue['name'],
                    'pic' => $bValue['pic'],
                    'gateway' => $value['gateway'],
                    'way' => $value['way'],
                    'type' => $bValue['bank'],
                    'rate' => $value['rate'],
                    'divide' => $value['divide'],
                ];
            }
            unset($payway[$key]);
        }
        foreach ($payway as $key => $value) {
            $payway["{$value['way']}_{$value['type']}"] = $value;
            unset($payway[$key]);
        }
        return $payway;
    }

    public function loadPayway($payway) {
        $this->cache(true);
        return $this->where(array('way' => $payway))->select()[0];
    }

    public function loadPayType($payway, $paytype) {
        $this->cache(true);
        $this->where([
            ['way' => $payway]
        ]);
        if ($paytype) {
            $this->where([
                ['type' => $paytype]
            ]);
        }
        $result = $this->select();
        if (count($result) == 0) {
            $result = $this->where([
                        ['way' => $payway],
                        ['type' => 'all'],
                    ])->select();
        }
        return $result[0];
    }

    public function getdivide($username, $payway, $paytype) {
        if ($paytype) {
            $tmp = $this->loadPayType($payway, $paytype);
        } else {
            $tmp = $this->loadPayway($payway);
        }
        $defaultDivide = $tmp['divide'];
        if (!$defaultDivide) {
            if ($paytype) {
                $tmp = $this->loadPayType($payway, 'all');
            } else {
                return false;
            }
            $defaultDivide = $tmp['divide'];
        }
        //下面和用户个人对比
        $md = D('divide');
        $tmp = $md->loadDivide($username, $payway, $paytype);
        if ($defaultDivide < $tmp) {
            return $tmp;
        } else {
            return $defaultDivide;
        }
    }

    public function loadUserPaywayList($username) {
        $pw = $this->getPayway();
        foreach ($pw as $key => $value) {
            $value['divide'] = $this->getdivide($username, $value['payway'], $value['payttype']);
            $pw[$key] = $value;
        }
        return $pw;
    }

}
