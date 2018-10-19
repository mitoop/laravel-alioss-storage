#Laravel AliyunOSS Storage Driver

> composer require mitoop/laravel-alioss-storage

```
 在 config/filesystems.php 的disk选项下加入 也可以修改默认驱动 default或者cloud 为oss 
 
 'oss' => [
            'driver'            => 'oss', // 使用的驱动 
            'access_key_id'     => env('ALI_OSS_ACCESS_ID', 'access_key_id'), // 阿里云oss access_key_id
            'access_key_secret' => env('ALI_OSS_ACCESS_KEY', 'access_key_secret'), // 阿里云oss access_key_secret
            'endpoint'          => env('ALI_OSS_ENDPOINT', 'endpoint'), // 阿里云oss endpoint
            'bucket'            => env('ALI_OSS_BUCKET', 'bucket'), // // 阿里云oss bucket
            'url_schema'        => env('ALI_OSS_URL_SCHEMA', 'http'), //  使用的url schema协议 可选项 http, https, both http=> 'http://url', https=>'https://url' both => '//url'
            'custom_domain'     => '', // 如果使用了自定义域名 此处写入自定义域名 如果没有 则为空或者不写该项
            
        ],
        
        
demo:
Storage::disk('oss')->put('file.tetx', 'hello world');
Storage::cloud()->put('file.tetx', 'hello world');
如果是默认驱动
Storage::put('file.tetx', 'hello world');
只在laravel 5.7下作了测试 目前兼容Storage方法, 适配器新增了signUrl方法 去除了putFile方法
对于getUrl方法作了调整, 去除了远端验证, 增加 url schrma 兼容处理, 删除方法去除了远端验证(阿里云oss为原子操作, 没必要).
```


Fork From (jacobcyl/Aliyun-oss-storage)[https://github.com/jacobcyl/Aliyun-oss-storage]
