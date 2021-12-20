<?php

namespace Laravel\Http\Request;
use Laravel\Http\Contracts\Http\IRequest;
use Laravel\Http\HttpException;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\TransferStats;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Http\Response\BaseResponse;

class BaseRequest implements IRequest
{
    protected $app;
    protected $options = [];
    /**
     * @var string 数据发送格式
     */
    protected $bodyFormat = 'json';
    /**
     * @var TransferStats 请求统计
     */
    protected $stat;
    /**
     * @var string 请求网关
     */
    protected $gatewayUrl;
    /**
     * @var int 重试次数
     */
    protected $retry;
    /**
     * @var int 重试间隔
     */
    protected $sleep;
    /**
     * @var array 服务配置
     */
    protected $config;
    protected $systemParams = [];
    protected $url;
    protected $args;

    public function __construct($app, array $config)
    {
        $this->app = $app;
        $this->config = $config;
        $this->gatewayUrl = $config['gateway_url'] ?? '';
        $this->retry = $config['retry'] ?? 0;
        $this->sleep = $config['sleep'] ?? 0;
        $this->options = [
            'timeout'     => $config['timeout'] ?? 3,
            'http_errors' => true,
            'on_stats'    => function (TransferStats $stats) {
                $this->stat = $stats;
            }
        ];
    }

    /**
     * Notes: get
     * @param $url
     * @param array $queryParams
     * @return BaseResponse|null
     * @throws BindingResolutionException
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:45
     */
    public function get($url, $queryParams = [])
    {
        $this->url = $url;
        $this->args = $queryParams;
        $response = $this->send('GET', $url, [
            'query' => array_merge($this->systemParams, $queryParams),
        ]);

        return $response;
    }

    /**
     * Notes: post
     * @param $url
     * @param array $params
     * @return \Laravel\Http\Response\BaseResponse|null
     * @throws BindingResolutionException
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:45
     */
    public function post($url, $params = [])
    {
        $this->url = $url;
        $this->args = $params;
        $response = $this->send('POST', $url, [
            $this->bodyFormat => array_merge($this->systemParams, $params),
        ]);

        return $response;
    }

    /**
     * Notes: patch
     * @param $url
     * @param array $params
     * @return \Laravel\Http\Response\BaseResponse|null
     * @throws BindingResolutionException
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:45
     */
    public function patch($url, $params = [])
    {
        $this->url = $url;
        $this->args = $params;
        $response = $this->send('PATCH', $url, [
            $this->bodyFormat => array_merge($this->systemParams, $params),
        ]);

        return $response;
    }

    /**
     * Notes: put
     * @param $url
     * @param array $params
     * @return BaseResponse|null
     * @throws BindingResolutionException
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:45
     */
    public function put($url, $params = [])
    {
        $this->url = $url;
        $this->args = $params;
        $response = $this->send('PUT', $url, [
            $this->bodyFormat => array_merge($this->systemParams, $params),
        ]);

        return $response;
    }

    /**
     * Notes: delete
     * @param $url
     * @param array $params
     * @return \Laravel\Http\Response\BaseResponse|null
     * @throws BindingResolutionException
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:45
     */
    public function delete($url, $params = [])
    {
        $this->url = $url;
        $this->args = $params;
        $response = $this->send('DELETE', $url, [
            $this->bodyFormat => array_merge($this->systemParams, $params),
        ]);

        return $response;
    }

    /**
     * Notes: asJson
     * @return \Laravel\Http\Request\BaseRequest
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:46
     */
    public function asJson()
    {
        return $this->bodyFormat('json')->contentType('application/json');
    }

    /**
     * Notes: asFormParams
     * @return \Laravel\Http\Request\BaseRequest
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:46
     */
    public function asFormParams()
    {
        return $this->bodyFormat('form_params')->contentType('application/x-www-form-urlencoded');
    }

    /**
     * Notes: contentType
     * @param $contentType
     * @return \Laravel\Http\Request\BaseRequest
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:46
     */
    protected function contentType($contentType)
    {
        return $this->withHeaders(['Content-Type' => $contentType]);
    }

    /**
     * Notes: bodyFormat
     * @param $format
     * @return $this
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:46
     */
    protected function bodyFormat($format)
    {
        $this->bodyFormat = $format;

        return $this;
    }

    /**
     * Notes: 合并参数
     * @param $options
     * @return $this
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:46
     */
    public function withOptions($options)
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * Notes: 设置cookie
     * @param array $cookie
     * @return $this
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:46
     */
    public function withCookie(array $cookie)
    {
        $this->options = array_merge($this->options, [
            'cookies' => $cookie,
        ]);

        return $this;
    }

    /**
     * Notes: 合并请求头
     * @param $headers
     * @return $this
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:48
     */
    public function withHeaders($headers)
    {
        if (!empty($this->options['headers'])) {
            $this->options['headers'] = array_merge($this->options['headers'], $headers);
        } else {
            $this->options = array_merge($this->options, [
                'headers' => $headers
            ]);
        }

        return $this;
    }

    /**
     * Notes: withPrefix
     * @param $str
     * @return $this
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:48
     */
    public function withPrefix($str)
    {
        return $this;
    }

    /**
     * Notes: 设置请求超时时间
     * @param $seconds
     * @return $this
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:49
     */
    public function timeout($seconds)
    {
        $this->options['timeout'] = $seconds;

        return $this;
    }

    /**
     * Notes: 设置重试次数
     * @param $retry
     * @return $this
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:49
     */
    public function retry($retry)
    {
        if ($retry >= 0) {
            $this->retry = $retry;
        }

        return $this;
    }

    /**
     * Notes: 设置重试间隔时间
     * @param $sleep
     * @return $this
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:49
     */
    public function sleep($sleep)
    {
        if ($sleep >= 0) {
            $this->sleep = $sleep;
        }

        return $this;
    }

    /**
     * Notes: getResponse
     * @param $method
     * @param $url
     * @param $options
     * @return \Laravel\Http\Response\BaseResponse
     * @throws GuzzleException
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:44
     */
    protected function getResponse($method, $url, $options)
    {
        return new BaseResponse($this, $this->buildClient()->request($method, $url, $this->mergeOptions([
            'query' => $this->parseQueryParams($url)
        ], $options)));
    }

    /**
     * Notes: send
     * @param $method
     * @param $url
     * @param $options
     * @return \Laravel\Http\Response\BaseResponse|null
     * @throws BindingResolutionException
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:45
     */
    public function send($method, $url, $options)
    {
        $response = null;
        beginning:
        try {
            $response = $this->getResponse($method, $url, $options);
        } catch (ConnectException $connectException) {
            $context = $connectException->getHandlerContext();

            // 超过重试次数则抛出异常
            if (!$this->retry) {
                throw new HttpException($context['errno'], '', [
                    'error'        => $context['error'],
                    'method'       => $method,
                    'url'          => $this->gatewayUrl . $this->getUrl(),
                    'args'         => $this->getArgs(),
                    'request_uri'  => app('request')->path(),
                    'request_args' => app('request')->all(),
                ]);
            }
            $this->retry--;
            if ($this->sleep) {
                usleep($this->sleep * 1000);
            }

            $this->logger([
                'method' => $method,
                'url'    => $this->gatewayUrl . $this->getUrl(),
                'args'   => $this->getArgs(),
                'errno'  => $context['errno'],
                'error'  => $context['error'],
                'retry'  => $this->retry,
                'sleep'  => $this->sleep
            ]);

            goto beginning;
        } catch (GuzzleException $exception) {
            throw new HttpException($exception->getCode(), '', [
                'error'        => $exception->getMessage(),
                'method'       => $method,
                'url'          => $this->gatewayUrl . $this->getUrl(),
                'args'         => $this->getArgs(),
                'request_uri'  => app('request')->path(),
                'request_args' => app('request')->all(),
            ]);
        }

        return $response;

    }

    /**
     * Notes: buildClient
     * @return Client
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:49
     */
    protected function buildClient()
    {
        return (new Client([
            'base_uri' => $this->gatewayUrl
        ]));
    }

    /**
     * Notes: mergeOptions
     * @param mixed ...$options
     * @return array
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:49
     */
    protected function mergeOptions(...$options)
    {
        $this->options = array_merge($this->options, ...$options);
        if ((data_get($this->options, 'cookies', []) instanceof CookieJar)) {
            $this->options['cookies'] = CookieJar::fromArray(data_get($this->options, 'cookies', []),
                parse_url($this->gatewayUrl)['host']);
        }

        return $this->options;
    }

    /**
     * Notes: parseQueryParams
     * @param $url
     * @return mixed
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:49
     */
    protected function parseQueryParams($url)
    {
        return tap([], function (&$query) use ($url) {
            parse_str(parse_url($url, PHP_URL_QUERY), $query);
        });
    }

    /**
     * Notes: getOptions
     * @return array
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:50
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Notes: getCookies
     * @return CookieJar|mixed
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:50
     */
    public function getCookies()
    {
        return $this->options['cookies'] ?? new CookieJar();
    }

    /**
     * Notes: getStat
     * @return TransferStats
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:50
     */
    public function getStat()
    {
        return $this->stat;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function finalTransformer($obj)
    {
        return $this;
    }

    /**
     * Notes: 记录重试调用日志
     * @param $data
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:50
     */
    protected function logger($data)
    {
        Log::getLogger(
            Str::lower(basename(str_replace('\\', '/', static::class))) . '.http.execute.info.retry'
        )->info($this->getUrl(), $data);
    }
}
