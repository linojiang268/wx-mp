<?php
require_once __DIR__.'/../vendor/autoload.php';

$template = '{{description.DATA}}测试参数1：{{param1.DATA}}测试参数2：{{param2.DATA}}测试参数3：{{param3.DATA}}测试参数4：{{param4.DATA}}{{remark.DATA}}';
$testAppId = '';
$testAppSecret = '';
$openid = '';
$templateId = '';

$service = new \Ouarea\WxMp\Service($testAppId, $testAppSecret);

list($accessToken, $expiresIn) = $service->getAccessToken();

// echo $accessToken . "\n" . $expiresIn;

$service->deleteMenu($accessToken);

$service->createMenu($accessToken, [
    "button" => [
        ['type' => 'click', 'name' => "测试1", 'key'  => 'test1',],
        ['type' => 'view', 'name' => "测试2", 'url'  => 'https://www.baidu.com',],
        ['type' => 'view', 'name' => "测试3", 'url'  => 'https://www.soso.com',],
    ]
]);

$service->sendTemplateMessage($accessToken, $openid, $templateId, [
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
