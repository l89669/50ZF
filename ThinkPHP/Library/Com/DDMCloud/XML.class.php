<?php

namespace Com\DDMCloud;

class XML {

    public static function array2XML($ar) {
        $xml = simplexml_load_string('<res />');
        self::createXML($ar, $xml);
        return $xml->saveXML();
    }

    public function createXML($ar, $xml) {
        foreach ($ar as $k => $v) {
            if (is_array($v)) {
                $x = $xml->addChild($k);
                self::createXML($v, $x);
            } else
                $xml->addChild($k, $v);
        }
    }

}
