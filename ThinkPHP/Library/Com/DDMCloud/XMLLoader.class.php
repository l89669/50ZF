<?php

namespace Com\DDMCloud;

/**
 * XML处理
 */
class XMLLoader {

    function __construct() {
        
    }

    /**
     * 最简单的XML转数组
     * @param string $xmlstring XML字符串
     * @return array XML数组
     */
    public static function xml_to_array($xmlstring) {
        return json_decode(json_encode((array) simplexml_load_string($xmlstring)), true);
    }

}
