<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/TestData.php';

$service = new \Ouarea\WxMp\Service(TestData::APPID, TestData::SECRET);

list($accessToken, $expiresIn) = $service->getAccessToken();

// echo $accessToken . "\n" . $expiresIn . "\n";

$service->deleteMenu($accessToken);

$service->createMenu($accessToken, [
    "button" => [
        ['type' => 'click', 'name' => "测试1", 'key'  => 'test1',],
        ['type' => 'view', 'name' => "测试2", 'url'  => 'https://www.baidu.com',],
        ['type' => 'view', 'name' => "测试3", 'url'  => 'https://www.soso.com',],
    ]
]);

$info = $service->getUserInfo($accessToken, TestData::OPENID);
var_dump($info);