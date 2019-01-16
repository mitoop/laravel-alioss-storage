<?php

namespace Mitoop\AliOSS;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use Mitoop\AliOSS\Plugins\PutRemoteFile;
use Mitoop\AliOSS\Plugins\SignUrl;
use OSS\OssClient;

class AliOSSServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    }

    public function boot()
    {
        Storage::extend('oss', function ($app, $config) {
            $accessId  = $config['access_key_id'];
            $accessKey = $config['access_key_secret'];
            $bucket    = $config['bucket'];
            $endPoint  = $config['endpoint'];

            $isCname      = isset($config['is_cname']) ? (bool)$config['is_cname'] : false;
            $customDomain = isset($config['custom_domain']) ? $config['custom_domain'] : '';
            $urlSchema    = isset($config['url_schema']) ? $config['url_schema'] : 'both';

            $client     = new OssClient($accessId, $accessKey, $endPoint, $isCname);
            $adapter    = new AliOSSAdapter($client, $bucket, $endPoint, $urlSchema, $customDomain);
            $filesystem = new Filesystem($adapter);
            $filesystem->addPlugin(new SignUrl());
            $filesystem->addPlugin(new PutRemoteFile());

            return $filesystem;
        });

    }
}