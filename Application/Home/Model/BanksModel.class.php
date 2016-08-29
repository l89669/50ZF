<?php

namespace Home\Model;

use Think\Model;
use Com\DDMCloud\Common;

class BanksModel extends Model {

    public function getBanks() {
        $this->cache(true);
        return $this->select();
    }

}
