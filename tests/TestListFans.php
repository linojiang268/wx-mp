<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/TestData.php';

$service = new \Ouarea\WxMp\Service(TestData::APPID, TestData::SECRET);

list($accessToken, $expiresIn) = $service->getAccessToken();

// echo $accessToken . "\n" . $expiresIn . "\n";

$rst = $service->listFans($accessToken, null);
var_dump($rst['total'], $rst['count'], $rst['openids'], $rst['next_openid']);

if ($rst['next_openid']) {
    $rst = $service->listFans($accessToken, $rst['next_openid']);
    var_dump($rst['total'], $rst['count'], $rst['openids'], $rst['next_openid']);
}