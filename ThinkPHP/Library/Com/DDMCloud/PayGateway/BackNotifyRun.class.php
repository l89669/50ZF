<?php

namespace Com\DDMCloud\PayGateway;

use Com\DDMCloud\HTTP;

class BackNotifyRun extends \Thread {

    public $url;
    public $data;

    public function __construct($url) {
        $this->url = "$url";
    }

    public function run() {
        if (($url = $this->url)) {
            $this->data = model_http_curl_get($this->url, NULL, NULL);
        }
    }

}

function model_http_curl_get($url, $params = array(), $method = 'GET', $extheaders = array(), $needHeader = false, $multi = false) {
    if (!function_exists('curl_init'))
        exit('Need to open the curl extension');
    $method = strtoupper($method);
    $ci = curl_init();
    curl_setopt($ci, CURLOPT_USERAGENT, 'PHP-SDK OAuth2.0');
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ci, CURLOPT_TIMEOUT, 3);
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ci, CURLOPT_HEADER, $needHeader);
    $headers = (array) $extheaders;
    switch ($method) {
        case 'POST':
            curl_setopt($ci, CURLOPT_POST, TRUE);
            if (!empty($params)) {
                if ($multi) {
                    foreach ($multi as $key => $file) {
                        $params[$key] = '@' . $file;
                    }
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
                    $headers[] = 'Expect: ';
                } else {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($params));
                }
            }
            break;
        case 'DELETE':
        case 'GET':
            $method == 'DELETE' && curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
            if (!empty($params)) {
                $url = $url . (strpos($url, '?') ? '&' : '?')
                        . (is_array($params) ? http_build_query($params) : $params);
            }
            break;
    }
    curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE);
    curl_setopt($ci, CURLOPT_URL, $url);
    if ($headers) {
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
    }

    $response = curl_exec($ci);
    curl_close($ci);
    return $response;
}
