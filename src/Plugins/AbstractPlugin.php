<?php

namespace Mitoop\AliOSS\Plugins;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\Plugin\AbstractPlugin as BaseAbstractPlugin;

abstract class AbstractPlugin extends BaseAbstractPlugin
{
    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var \Mitoop\AliOSS\Adapter
     */
    protected $adapter;

    /**
     * Set the Filesystem object.
     */
    public function setFilesystem(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->adapter = $filesystem->getAdapter();
    }
}
