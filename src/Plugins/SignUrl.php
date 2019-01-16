<?php

namespace Mitoop\AliOSS\Plugins;

use League\Flysystem\Plugin\AbstractPlugin;
use OSS\OssClient;

class SignUrl extends AbstractPlugin
{

    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'signUrl';
    }

    public function handle($object, $timeout = 60 * 60 * 8, $method = OssClient::OSS_HTTP_GET)
    {
        return $this->filesystem->getAdapter()->signUrl($object, $timeout, $method);
    }
}