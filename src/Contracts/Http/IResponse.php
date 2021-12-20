<?php

namespace Laravel\Http\Contracts\Http;

interface IResponse
{
    public function header($header);


    /**
     * Notes: 所有请求头
     * @return mixed
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 10:50
     */
    public function headers();


    /**
     * Notes: 返回状态
     * @return mixed
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 10:51
     */
    public function status();


    /**
     * Notes: cookies
     * @return mixed
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 10:51
     */
    public function getCookies();


    /**
     * Notes: 获取制定cookie
     * @param $name
     * @return mixed
     * @author: Rex.栗田庆
     * @Date: 2020-07-20
     * @Time: 10:51
     */
    public function getCookie($name);
}