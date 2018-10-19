<?php

namespace Mitoop\AliOSS;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
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

            $customDomain = empty($config['custom_domain']) ? '' : $config['custom_domain'];
            $urlSchema    = empty($config['url_schema']) ? 'both' : $config['url_schema'];

            $client     = new OssClient($accessId, $accessKey, $endPoint, (bool)$customDomain);
            $adapter    = new AliOSSAdapter($client, $bucket, $endPoint, $urlSchema, $customDomain);
            $filesystem = new Filesystem($adapter);
            $filesystem->addPlugin(new SignUrl());
            $filesystem->addPlugin(new PutRemoteFile());

            return $filesystem;
        });

    }
}