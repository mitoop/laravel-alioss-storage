# Laravel Aliyun OSS Filesystem Storage

## Install
> composer require mitoop/laravel-alioss-storage

Laravel 版本低于5.5版本 请添加`Mitoop\AliOSS\ServiceProvider` 到 `config/app.php` 的 `providers` 数组

Laravel 9 起 依赖 `league/flysystem` 版本发生变化, 接口约束也发生了变化

## Require
   - > Laravel 5+ && < Laravel 9

## Configure
在 `config/filesystems.php` 的 `disk`里增加配置:
```
 'oss' => [ // 定义的disk名称, 自定义，只要不重复就行
    'driver'            => 'oss', // 使用的驱动，这里只能填写 `oss` [必填]
    'access_key_id'     => env('OSS_ACCESS_ID', 'access_key_id'), // OSS access_key_id [必填]
    'access_key_secret' => env('OSS_ACCESS_KEY', 'access_key_secret'), // OSS access_key_secret [必填]
    'endpoint'          => env('OSS_ENDPOINT', 'endpoint'), // OSS endpoint [必填]
    'bucket'            => env('OSS_BUCKET', 'bucket'), // OSS bucket [必填]
    'custom_domain'     => 'http://foo.bar', // 自定义域名， 参考下方说明
    'schema'            => env('OSS_SCHEMA', 'https'), // 使用默认域名时用的 url schema, 参考下方说明
    'plugins'           => [], // 可以自己封装插件，实现更多方法 参考下方说明
 ],

  说明:
  1. 自定义域名
  获取 OSS URL 的时候，默认地址是 bucket.endpoint/path，指定 schema 后为 http(s)://bucket.endpoint/path
  当使用自定义域名后, 就不再使用默认地址，schema 此时就不起作用了, 获取地址变为：自定义域名/path
  
  2. 插件
  如果要实现更多方法，在 `plugins` 里添加上对应类， 例如
  'plugins' => [
      DoSomethingPlugin::class,
  ],
  插件类应该继承 `\Mitoop\AliOSS\PluginsAbstractPlugin` 
  或者继承 `League\Flysystem\Plugin\AbstractPlugin`
```  
      
## Use
可以使用 Laravel Storage 的所有方法

集成插件提供的方法 :
- signUrl 
  ```
  使用签名URL进行临时授权(主要对私有对象使用). 
  
  Storage::disk('oss')->signUrl($path, $timeout);
  ```
- putRemoteFile 
  ```
  将本地文件或者远程URL文件存储到oss. 
  
  Storage::disk('oss')->putRemoteFile($path, $remoteUrl);
  ```

## Notice

1. `deleteDir` 删除文件夹方法. 方法直接返回为 false, 不会进行删除，如果要删除文件夹强烈推荐到阿里云后台操作.

2. `listContents` 列出文件夹目录(支持递归)方法. 方法直接返回为空数组 [], 如果有此业务，可以考虑通过插件实现.

3. 除了 `has` 方法, 所有方法在失败的时候都会返回 false (不抛出异常，但有日志记录),
   
   如果需要, 你可以用 === false 来判断是否成功. 配置正常的情况下, 失败概率极低.

3. `has` 方法, 本身返回 true / false, 所以发生错误会抛出异常.

4. `$request->file('avatar')->store('avatars');` 上传文件直接 `store` 就生成随机名称，这里的 `avatars` 只是目录名称

   所以推荐使用 `storeAs` 方法来达到预期的目的.
   
   曾经遇到过 `store` 方法生成随机名称获取扩展的时候，对于 WPS 的 docx/pptx, 总是获取不到正确的文件扩展名称
   
   最后 `storeAs` 手动解决了.

5. `temporaryUrl` 方法和 `signUrl` 是一样的效果, 区别仅在于第二个参数

   `signUrl` 传入的 int 类型, 例如, 传入30表示30秒后过期
   
   `temporaryUrl` 传入的是 `\DateTimeInterface` 类型, 例如, 传入 now()->addSeconds(30) 也是表示30秒后过期

## More

Storage方法通常会提供 `options` 参数. 最常用的就是设置文件可见性.

设置可见性：
```
// 只设置可见行，直接 public
Storage::disk('oss')->put('file.jpg', $contents, 'public');
或
// 还有其他 option 配置时使用 ['visibility' => 'private']
Storage::disk('oss')->put('file.jpg', $contents, ['visibility' => 'private']) 

public 对应 OSS 的公开读权限
private 对应 OSS 的私有权限
不单独设置可见性, 默认继承 bucket 的可见性 
```

## Links
[https://github.com/laravel/framework/tree/7.x/src/Illuminate/Filesystem](https://github.com/laravel/framework/tree/7.x/src/Illuminate/Filesystem)

[https://github.com/thephpleague/flysystem](https://github.com/thephpleague/flysystem)

[https://help.aliyun.com/document_detail/32099.html](https://help.aliyun.com/document_detail/32099.html)

[https://github.com/jacobcyl/Aliyun-oss-storage](https://github.com/jacobcyl/Aliyun-oss-storage)

[https://github.com/thephpleague/flysystem-aws-s3-v3](https://github.com/thephpleague/flysystem-aws-s3-v3)

[https://github.com/apollopy/flysystem-aliyun-oss](https://github.com/apollopy/flysystem-aliyun-oss)

## License
```
        DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE 
                    Version 2, December 2004 

 Copyright (C) 2004 Sam Hocevar <sam@hocevar.net> 

 Everyone is permitted to copy and distribute verbatim or modified 
 copies of this license document, and changing it is allowed as long 
 as the name is changed. 

            DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE 
   TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION 

  0. You just DO WHAT THE FUCK YOU WANT TO.
```
