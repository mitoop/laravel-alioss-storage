<?php

namespace Mitoop\AliOSS;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use League\Flysystem\Filesystem;
use Mitoop\AliOSS\Plugins\PutRemoteFile;
use Mitoop\AliOSS\Plugins\SignUrl;
use OSS\OssClient;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        Storage::extend('oss', function ($app, $config) {
            $accessId = $config['access_key_id'];
            $accessKey = $config['access_key_secret'];
            $bucket = $config['bucket'];
            $endPoint = $config['endpoint'];
            $customDomain = isset($config['custom_domain']) ? $config['custom_domain'] : false;
            $schema = isset($config['schema']) ? $config['schema'] : 'https';

            $client = new OssClient($accessId, $accessKey, $endPoint);
            $adapter = new Adapter($client, $bucket, $endPoint, $schema, $customDomain);
            $filesystem = new Filesystem($adapter);
            $filesystem->addPlugin(new SignUrl());
            $filesystem->addPlugin(new PutRemoteFile());

            $plugins = isset($config['plugins']) ? (array) $config['plugins'] : [];
            foreach ($plugins as $plugin) {
                $filesystem->addPlugin(new $plugin());
            }

            return $filesystem;
        });
    }
}
