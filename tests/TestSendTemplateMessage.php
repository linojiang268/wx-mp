<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/TestData.php';

$service = new \Ouarea\WxMp\Service(TestData::APPID, TestData::SECRET);

list($accessToken, $expiresIn) = $service->getAccessToken();

// echo $accessToken . "\n" . $expiresIn . "\n";

$service->sendTemplateMessage($accessToken, TestData::OPENID, TestData::TEMPLATE_ID, [
    'description' => [
        'value' => "Test Description\n\n",
    ],
    'param1' => [
        'value' => "Test Param1\n",
    ],
    'param2' => [
        'value' => "Test Param2\n",
    ],
    'param3' => [
        'value' => "Test Param3\n",
    ],
    'param4' => [
        'value' => "Test Param4\n",
    ],
    'remark' => [
        'value' => '',
    ],
], null, ['url' => 'https://www.baidu.com']);