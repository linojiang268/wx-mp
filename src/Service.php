<?php

namespace Ouarea\WxMp;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;

class Service
{
    /**
     * Url to get access token
     */
    const GET_ACCESS_TOKEN_URL      = 'https://api.weixin.qq.com/cgi-bin/token';

    /**
     * Url to delete menu in mp
     */
    const DELETE_MENU_URL           = 'https://api.weixin.qq.com/cgi-bin/menu/delete';

    /**
     * Url to create menu in mp
     */
    const CREATE_MENU_URL           = 'https://api.weixin.qq.com/cgi-bin/menu/create';

    /**
     * Url to get ticket in mp
     */
    const GET_TICKET_URL            = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket';

    /**
     * Url to send template message
     */
    const SEND_TEMPLATE_MESSAGE_URL = 'https://api.weixin.qq.com/cgi-bin/message/template/send';

    /**
     * Url to longToShort
     */
    const LONG_TO_SHORT_URL = 'https://api.weixin.qq.com/cgi-bin/shorturl';

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * App id
     *
     * @var string
     */
    private $appId;

    /**
     * App secret
     *
     * @var string
     */
    private $appSecret;

    public function __construct($appId, $appSecret, ClientInterface $client = null)
    {
        $this->appId     = $appId;
        $this->appSecret = $appSecret;

        $this->client    = $client ?: $this->createDefaultHttpClient();
    }

    /**
     *
     * Get access token from wx-mp
     *
     * @return array  [accessToken. expiresIn]
     * @throws \Exception
     */
    public function getAccessToken()
    {
        $responseObj = $this->sendGetRequestAndDecode($this->buildRequestUrlForGetAccessToken());

        if (isset($responseObj['errcode']) && 0 != $responseObj['errcode']) {
            throw new \Exception($responseObj['errmsg']);
        }

        return [$responseObj['access_token'], $responseObj['expires_in']];
    }

    /**
     * Delete menu
     *
     * @param $accessToken
     * @throws \Exception
     */
    public function deleteMenu($accessToken)
    {
        list($url, $params) = $this->buildRequestUrlAndParamsForDeleteMenu($accessToken);
        $responseObj = $this->sendPostRequestAndDecode($url, $params);

        if (0 != $responseObj['errcode']) {
            throw new \Exception($responseObj['errmsg']);
        }
    }

    /**
     * Create Menu
     *
     * @param $accessToken
     * @param array $menu  array keys taken:
     *                       "button" => [
     *                          ['type' => 'click', 'name' => "测试1", 'key'  => 'test1',],
     *                          ['type' => 'view', 'name' => "测试2", 'url'  => 'https://www.baidu.com',],
     *                          ['type' => 'view', 'name' => "测试3", 'url'  => 'https://www.soso.com',],
     *                          ["name": "button", "sub_button" => [
     *                              "list": [
     *                                  [
     *                                      "type": "view",
     *                                      "name": "测试sub",
     *                                      "url": "http://www.qq.com"
     *                                  ],
     *                              ]
     *                          ]
     * @throws \Exception
     */
    public function createMenu($accessToken, array $menu = [])
    {
        list($url, $params) = $this->buildRequestUrlAndParamsForCreateMenu($accessToken, $menu);
        $responseObj = $this->sendPostRequestAndDecode($url, $params);

        if (0 != $responseObj['errcode']) {
            throw new \Exception($responseObj['errmsg']);
        }
    }

    /**
     * Get ticket
     *
     * @param $accessToken
     * @return array [ticket, expires_in]
     * @throws \Exception
     */
    public function getTicket($accessToken)
    {
        $responseObj = $this->sendGetRequestAndDecode($this->buildRequestUrlAndParamsForGetTicket($accessToken));

        if (isset($responseObj['errcode']) && 0 != $responseObj['errcode']) {
            throw new \Exception($responseObj['errmsg']);
        }

        return [$responseObj['ticket'], $responseObj['expires_in']];
    }

    /**
     * Get signature
     *
     * @param $ticket
     * @param $url
     * @return array [app_id, timestamp, nonce_str, signature]
     */
    public function getSignatureInfo($ticket, $url)
    {
        $params = [
            'jsapi_ticket' => $ticket,
            'noncestr'     => str_random(16),
            'timestamp'    => time(),
            'url'          => ($index = strpos($url,'#')) ? substr($url, 0, $index) : $url,
        ];

        $pieces = [];
        foreach ($params as $k => $v) {
            array_push($pieces, sprintf("%s=%s", $k, $v));
        }

        return [
            'app_id'    => $this->appId,
            'timestamp' => $params['timestamp'],
            'nonce_str' => $params['noncestr'],
            'signature' => sha1(implode('&', $pieces)),

        ];
    }

    /**
     * @param $accessToken
     * @param $touser         openid of touser
     * @param $templateId
     * @param array $data     values like:
     *                         [
     *                              'price' => [
     *                                  'value' => '39.8元',
     *                                  'color' => '#173177',
     *                              ]
     *                         ]
     * @param null $color
     * @param array $options  array keys taken:
     *                         - url
     *                         - miniprogram
     *                         - - appid
     *                         - - pagepath
     * @throws \Exception
     */
    public function sendTemplateMessage($accessToken, $touser, $templateId, array $data, $color = null, array $options = [])
    {
        list($url, $params) = $this->buildRequestUrlAndParamsForSendTemplateMessage($accessToken, $touser, $templateId, $data, $color, $options);
        $responseObj = $this->sendPostRequestAndDecode($url, $params);

        if (0 != $responseObj['errcode']) {
            throw new \Exception($responseObj['errmsg']);
        }
    }

    private function buildRequestUrlForGetAccessToken()
    {
        return self::GET_ACCESS_TOKEN_URL . '?' . http_build_query([
                'grant_type' => 'client_credential',
                'appid'      => $this->appId,
                'secret'     => $this->appSecret,
            ]);
    }

    /**
     * @param $accessToken
     * @return array  [url, params]
     */
    private function buildRequestUrlAndParamsForDeleteMenu($accessToken)
    {
        return [
            self::DELETE_MENU_URL . '?access_token=' . $accessToken, []
        ];
    }

    /**
     * @param $accessToken
     * @param array $menu
     * @return array [url, params]
     */
    private function buildRequestUrlAndParamsForCreateMenu($accessToken, array $menu = [])
    {
        return [
            self::CREATE_MENU_URL . '?access_token=' . $accessToken, $menu,
        ];
    }

    /**
     * @param $accessToken
     * @return string
     */
    private function buildRequestUrlAndParamsForGetTicket($accessToken)
    {
        return self::GET_TICKET_URL . '?' . http_build_query([
                'access_token' => $accessToken,
                'type'         =>'jsapi',
            ]);
    }

    /**
     * @param $accesToken
     * @param $touser
     * @param $templateId
     * @param array $data
     * @param null $color
     * @param array $options
     * @return array  [url, params]
     */
    private function buildRequestUrlAndParamsForSendTemplateMessage($accesToken, $touser, $templateId, array $data, $color = null, array $options = [])
    {
        return [
            self::SEND_TEMPLATE_MESSAGE_URL . '?access_token=' . $accesToken,
            array_filter([
                'touser'      => $touser,
                'template_id' => $templateId,
                'data'        => $data,
                'color'       => $color,
                'url'         => array_get($options, 'url'),
                'miniprogram' => array_get($options, 'miniprogram'),
            ]),
        ];
    }

    // issue request and decode the response
    private function sendGetRequestAndDecode($url)
    {
        $options = [
            RequestOptions::TIMEOUT => 500,
            RequestOptions::VERIFY  => false,
        ];

        $response = $this->client->request('GET', $url, $options);
        $responseBody = $response->getBody()->getContents();
        if ($response->getStatusCode() != 200) {
            throw new \Exception(sprintf('微信服务异常: %s', $responseBody));
        }

        if (false === ($response = json_decode($responseBody, true))) {
            throw new \Exception(sprintf('响应异常: %s', $responseBody));
        }

        return $response;
    }

    private function sendPostRequestAndDecode($url, $params)
    {
        $options = [
            RequestOptions::TIMEOUT => 500,
            RequestOptions::VERIFY  => false,
            RequestOptions::BODY    => json_encode($params, JSON_UNESCAPED_UNICODE),
        ];

        $response = $this->client->request('POST', $url, $options);
        $responseBody = $response->getBody()->getContents();
        if ($response->getStatusCode() != 200) {
            throw new \Exception(sprintf('微信服务异常: %s', $responseBody));
        }

        if (false === ($response = json_decode($responseBody, true))) {
            throw new \Exception(sprintf('响应异常: %s', $responseBody));
        }

        return $response;
    }

    /**
     * Long url to short url
     *
     * @param $accessToken
     * @param $longUrl
     * @return mixed
     * @throws \Exception
     */
    public function long2ShortUrl($accessToken, $longUrl)
    {
        $responseObj = $this->sendPostRequestAndDecode(self::LONG_TO_SHORT_URL . '?access_token=' . $accessToken, [
            'action'   => 'long2short',
            'long_url' => $longUrl,
        ]);

        if (isset($responseObj['errcode']) && 0 != $responseObj['errcode']) {
            throw new \Exception($responseObj['errmsg']);
        }

        return $responseObj['short_url'];
    }

    /**
     * create default http client
     *
     * @return Client
     */
    private function createDefaultHttpClient()
    {
        return new Client();
    }
}