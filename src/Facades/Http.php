<?php
namespace Laravel\Http\Facades;

use Illuminate\Support\Facades\Facade;
/**
 * 创建人：Rex.栗田庆
 * 创建时间：2019-05-13 19:48
 * Class HTTP 服务 Facade
 * @method static \Laravel\Http\Request\BaseRequest get($url, $queryParams = [])
 * @method static \Laravel\Http\Request\BaseRequest with($driver)
 * @method static \Laravel\Http\Request\BaseRequest post($url, $params = [])
 * @method static \Laravel\Http\Request\BaseRequest patch($url, $params = [])
 * @method static \Laravel\Http\Request\BaseRequest put($url, $params = [])
 * @method static \Laravel\Http\Request\BaseRequest delete($url, $params = [])
 * @method static \Laravel\Http\Request\BaseRequest asJson()
 * @method static \Laravel\Http\Request\BaseRequest asFormParams()
 * @method static \Laravel\Http\Request\BaseRequest contentType($contentType)
 * @method static \Laravel\Http\Request\BaseRequest withOptions($options)
 * @method static \Laravel\Http\Request\BaseRequest withCookie(array $cookie)
 * @method static \Laravel\Http\Request\BaseRequest withHeaders($headers)
 * @method static \Laravel\Http\Request\BaseRequest timeout($seconds)
 * @method static \Laravel\Http\Request\BaseRequest retry($retry)
 * @method static \Laravel\Http\Request\BaseRequest sleep($sleep)
 * @method static \Laravel\Http\Request\BaseRequest send($method, $url, $options)
 * @method static array getOptions()
 * @method static \GuzzleHttp\Cookie\CookieJar getCookies()
 * @see \Laravel\Http\Request\SiheRequest
 */
class Http extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'http';
    }
}
