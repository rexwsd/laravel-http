# 驱动化管理HTTP服务Libray


## Install

### 方式一

> composer require rexwsd/laravel-http

### 方式二
Step1: 在项目composer.json文件require段中增加如下：


```php
"require": {
         "rexwsd/laravel-http": "*"
     }
```

Step2: 执行  
> composer update rexwsd/laravel-http

## Document
### config配置

> 拷贝包内config/http.php  到项目下 config/http.php

#### 自定义驱动
```php
'gao_de' => [
        'gateway_url' => 'http://restapi.amap.com',
        'debug' => 'false',
        'timeout' => 1, // 全局超时时长
        'retry' => 1, // 全局重试次数
        'sleep' => 0, // 睡眠时间,只有开启重试后会启用
    ]
```

---

### http管理类

> 在 App\Components\Http 创建 HttpManager 类 (路径不存在自行创建)

```php
class HttpManager extends \Laravel\Http\HttpManager
{
    public function createGaoDeDriver()
    {
        return new GaoDeRequest($this->app, $this->getConfig('gao_de'));
    }
}
```

> 在 App\Components\Http\Requests 创建自定义响应类 GaoDeRequest 继承 Laravel\Http\Request\SiheRequest

```php

class GaoDeRequest extends BaseRequest
{
    //这里面你可以重写一些父类的方法: 例如 get  post send 等 根据你自己的业务需求 当然你也可以不写直接食用
}

```

### Provider 注册

> 在 App\Providers 的 AppServiceProvider 类里注册

```php
use App\Components\Http\HttpManager;

public function register()
    {
        $this->app->singleton('http', function () {
            return new HttpManager($this->app);
        });

    }
```
> 这样我们就把我们自定义的HttpManager覆盖注册到服务容器里了, 我这个是注册了一个调用高德api的接口例子

### 食用范例

```php
use Laravel\Http\Facades\Http;

$parameters = [
            'output' => 'json',
            'location' => "116.543302,39.923348" //公司坐标
        ];
        $res = Http::with('gao_de') //通过哪个驱动发起http请求
            ->timeout(1)  //请求超时时间 单位秒
            ->retry(1) //请求超时重试次数
            ->get('/v3/geocode/regeo', $parameters)->toArray(); //发起一个get请求

        dd($res);//打印请求结果
```
