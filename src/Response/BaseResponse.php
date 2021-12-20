<?php

namespace Laravel\Http\Response;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use Laravel\Http\Contracts\Http\IResponse;
use Psr\Http\Message\ResponseInterface;
use Laravel\Http\Request\BaseRequest;
use Laravel\Log\Facades\Log;
class BaseResponse implements IResponse, Arrayable, \ArrayAccess
{


    protected $response;

    protected $siheRequest;
    /**
     * @var array 请求响应内容
     */
    protected $contents;
    /**
     * @var int 格式化时间时间
     */
    protected $formatTime;

    protected $result;

    /**
     * SiheResponse 构造函数.
     * @param BaseRequest $request
     * @param ResponseInterface $response
     */
    public function __construct(BaseRequest $request, ResponseInterface $response)
    {
        $this->siheRequest = $request;
        $this->response = $response;
        $this->result = new \stdClass();
        $this->formatContents();
    }

    /**
     * Notes: 格式化内容
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:01
     */
    protected function formatContents()
    {
        $formatTime = microtime(1);
        $this->contents = (array)json_decode($this->response->getBody(), true);
        $this->formatTime = round(microtime(1) - $formatTime, 4);
    }


    /**
     * 创建http响应
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function toResponse()
    {
        $this->result->code = '0';
        $this->result->message = '成功';
        $this->result->data = $this->toArray();
        return response()->json($this->result);
    }

    public function toArray()
    {
        $data = $this->contents;
        $stat = $this->siheRequest->getStat();
        $data['info'] = array();
        //格式化数据时间
        $data['info']['format_time'] = $this->formatTime;
        //建立连接所消耗的时间
        $data['info']['connect_time'] = $stat->gethandlerStats()['connect_time'];
        //数据传输所消耗的时间
        $data['info']['down_time'] = $stat->gethandlerStats()['starttransfer_time'];
        //下载数据量的大小
        $data['info']['down_size'] = $stat->gethandlerStats()['size_download'];
        //平均下载速度
        $data['info']['down_speed'] = $stat->gethandlerStats()['speed_download'];
        //总的消耗时间
        $data['info']['total_time'] = $stat->gethandlerStats()['total_time'];
        //请求方法
        $data['info']['method'] = $this->siheRequest->getUrl();
        //请求参数
        $data['info']['args'] = $this->siheRequest->getArgs();
        //请求配置
        $data['info']['config'] = $this->siheRequest->getConfig();
        //请求头信息
        $data['info']['headers'] = data_get($this->siheRequest->getOptions(), 'headers', []);
        //REST请求地址
        $data['info']['request_uri'] = app('request')->path();
        //REST请求参数
        $data['info']['request_args'] = app('request')->all();

        $this->logger($data);
        // 开启 debug 输出统计数据
        if (!$this->siheRequest->getConfig()['debug']) {
            unset($data['info']);
        }

        return $data;
    }

    /**
     * Notes: 把响应值转为数组
     * @return array
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:18
     */
    public function getContent()
    {
        return $this->toArray();
    }


    /**
     * Notes: header
     * @param $header
     * @return string
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:20
     */
    public function header($header)
    {
        return $this->response->getHeaderLine($header);
    }


    /**
     * Notes: 返回所有头信息
     * @return array|mixed
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:20
     */
    public function headers()
    {
        return array_map(function ($item) {
            return $item[0];
        }, $this->response->getHeaders());
    }


    /**
     * Notes: 获取响应状态码
     * @return int|mixed
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:20
     */
    public function status()
    {
        return $this->response->getStatusCode();
    }


    /**
     * Notes: 获取cookie
     * @return CookieJar|mixed
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:21
     */
    public function getCookies()
    {
        return $this->siheRequest->getCookies();
    }


    /**
     * Notes: 获取制定cookie
     * @param $name
     * @return SetCookie|mixed|null
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:21
     */
    public function getCookie($name)
    {
        return $this->getCookies()->getCookieByName($name) ?? new SetCookie();
    }

    /**
     * 写入日志
     */
    protected function logger($data)
    {
        // 防止返回大内容写入文件
        Log::getLogger(
            Str::lower(basename(str_replace('\\', '/', get_class($this->siheRequest)))) . '.http.execute.info'
        )->info($this->siheRequest->getUrl(), $data);
    }

    public function offsetExists($key)
    {
        return array_key_exists($key, $this->contents);
    }

    public function offsetGet($key)
    {
        return $this->contents[$key] ?? null;
    }

    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $this->contents[] = $value;
        } else {
            $this->contents[$key] = $value;
        }
    }

    public function offsetUnset($key)
    {
        unset($this->contents[$key]);
    }

    public function __call($method, $args)
    {
        return $this->response->{$method}(...$args);
    }
}