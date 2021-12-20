<?php

namespace Laravel\Http;

use Illuminate\Http\Exceptions\HttpResponseException;
use Laravel\Log\Facades\Log;

/**
 * HTTP异常处理
 * Class HttpException
 * @package Sihe\Http
 */
class HttpException extends HttpResponseException
{
    protected $data;

    public function __construct($code, $message = '', $data = [])
    {
        $this->data = $data;
        $return = [
            'code' => $code,
            'msg' => $message ?: '网络异常,请联系开发者.',
            'data' => [
                'error' => data_get($data, 'error', '')
            ],
        ];
        $this->report($data);

        $result = new \stdClass();
        $result->code = $code ?? 'HTTP_ERR';
        $result->message = $return['msg'];
        $result->data = $return['data'];
        parent::__construct(response()->json($result, 200));
    }

    /**
     * Notes: 记录异常日志
     * @param $data
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 11:32
     */
    public function report($data)
    {
        Log::getLogger('http.exception')->error(static::class, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
