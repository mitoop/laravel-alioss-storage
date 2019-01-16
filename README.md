# Laravel filesystem Aliyun OSS

Fork From [jacobcyl/Aliyun-oss-storage](https://github.com/jacobcyl/Aliyun-oss-storage)

## 安装
> composer require mitoop/laravel-alioss-storage

Laravel5.5+ 开始支持包自动发现 如果低于5.5版本 请添加`Mitoop\AliOSS\AliOSSServiceProvider` 到 `config/app.php` 的 `providers` 数组

## Require
   - Laravel 5+
   - cURL extension

## 配置
所有配置都在 `config/filesystems.php` 里
```
 在 config/filesystems.php 的disk选项下加入oss
 
 'oss' => [
            'driver'            => 'oss', // 使用的驱动 必填
            'access_key_id'     => env('ALI_OSS_ACCESS_ID', 'access_key_id'), // 阿里云oss access_key_id 必填
            'access_key_secret' => env('ALI_OSS_ACCESS_KEY', 'access_key_secret'), // 阿里云oss access_key_secret 必填
            'endpoint'          => env('ALI_OSS_ENDPOINT', 'endpoint'), // 阿里云oss endpoint 必填
            'bucket'            => env('ALI_OSS_BUCKET', 'bucket'), // 阿里云oss bucket 必填
            'url_schema'        => env('ALI_OSS_URL_SCHEMA', 'both'), // 使用的url schema协议 可选项 http, https, both(url为兼容模式: //url) 非必填 默认值为both
            'is_cname'          => true 或者 false, 是否使用自定义域名 这个结合自己的阿里云配置 非必填
            'custom_domain'     => '', // 自定义获取对象链接时候的域名 非必填
 ],
 
 可以通过更改`default`配置为`oss` 设置`oss`为默认值
```  
      
## 使用
Laravel Storage API的所有方法
两个插件提供的方法 :
- signUrl 使用签名URL进行临时授权
- putRemoteFile 将本地文件或者远程URL文件存储到oss