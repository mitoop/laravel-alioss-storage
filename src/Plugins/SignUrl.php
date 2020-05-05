<?php

namespace Mitoop\AliOSS\Plugins;

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

    public function handle($object, $timeout = 60 * 60 * 8, array $options = [])
    {
        $expiration = now()->addSeconds($timeout);

        return $this->adapter->getTemporaryUrl($object, $expiration, $options);
    }
}
