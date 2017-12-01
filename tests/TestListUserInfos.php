<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/TestData.php';

$service = new \Ouarea\WxMp\Service(TestData::APPID, TestData::SECRET);

list($accessToken, $expiresIn) = $service->getAccessToken();

// echo $accessToken . "\n" . $expiresIn . "\n";

$userInfos = $service->listUserInfos($accessToken, [TestData::OPENID]);
var_dump($userInfos);