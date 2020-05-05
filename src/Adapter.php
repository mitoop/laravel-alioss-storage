<?php

namespace Mitoop\AliOSS;

use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Adapter\CanOverwriteFiles;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use League\Flysystem\Util;
use Log;
use OSS\Core\OssException;
use OSS\OssClient;
use Throwable;

class Adapter extends AbstractAdapter implements CanOverwriteFiles
{
    /**
     * @var OssClient oss client
     */
    private $client;

    /**
     * @var string bucket name
     */
    private $bucket;

    /**
     * @var string endpoint link
     */
    private $endPoint;

    /**
     * @var string url schema
     */
    private $schema;

    /**
     * @var string custom domain for show
     */
    private $customDomain;

    /**
     * @var array options
     */
    private $options = [
        'Multipart' => 128,
    ];

    /**
     * View Aliyun OSS Setting.
     *
     * @var array
     */
    protected static $optionsMap = [
        'mimetype' => OssClient::OSS_CONTENT_TYPE, // alias of content-type.
        'content-type' => OssClient::OSS_CONTENT_TYPE,
        'content-encoding' => 'Content-Encoding',
        'content-language' => 'Content-Language',
        'content-disposition' => OssClient::OSS_CONTENT_DISPOSTION,
        'cache-control' => OssClient::OSS_CACHE_CONTROL,
        'expires' => OssClient::OSS_EXPIRES,
        'visibility' => OssClient::OSS_OBJECT_ACL,
    ];

    public function __construct(OssClient $client, $bucket, $endPoint, $schema, $customDomain)
    {
        $this->client = $client;
        $this->bucket = $bucket;
        $this->endPoint = $endPoint;
        $this->schema = $schema;
        $this->customDomain = $customDomain;
    }

    /**
     * write.
     *
     * @param string $path
     * @param string $contents
     *
     * @return array|bool|false
     */
    public function write($path, $contents, Config $config)
    {
        $path = $this->applyPathPrefix($path);

        $options = $this->getOptions($config);

        if (! isset($options[OssClient::OSS_LENGTH])) {
            $options[OssClient::OSS_LENGTH] = Util::contentSize($contents);
        }

        if (! isset($options[OssClient::OSS_CONTENT_TYPE])) {
            $options[OssClient::OSS_CONTENT_TYPE] = Util::guessMimeType($path, $contents);
        }

        try {
            $this->client->putObject($this->bucket, $path, $contents, $options);
        } catch (Throwable $t) {
            $this->logException(__FUNCTION__, $t);

            return false;
        }

        return true;
    }

    /**
     * Write stream.
     *
     * @param string   $path
     * @param resource $resource
     *
     * @return array|bool|false
     */
    public function writeStream($path, $resource, Config $config)
    {
        $contents = stream_get_contents($resource);

        return $this->write($path, $contents, $config);
    }

    /**
     * Update, it's just overwritten.
     *
     * @param string $path
     * @param string $contents
     *
     * @return array|bool|false
     */
    public function update($path, $contents, Config $config)
    {
        return $this->write($path, $contents, $config);
    }

    /**
     * Write stream, it's just overwritten stream.
     *
     * @param string   $path
     * @param resource $resource
     *
     * @return array|bool|false
     */
    public function updateStream($path, $resource, Config $config)
    {
        return $this->writeStream($path, $resource, $config);
    }

    /**
     * Rename means copy and delete.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function rename($path, $newpath)
    {
        if (false === $this->copy($path, $newpath)) {
            return false;
        }

        $this->delete($path);

        return true;
    }

    /**
     * Copy.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function copy($path, $newpath)
    {
        $path = $this->applyPathPrefix($path);
        $newpath = $this->applyPathPrefix($newpath);

        try {
            $this->client->copyObject($this->bucket, $path, $this->bucket, $newpath);
        } catch (Throwable $t) {
            $this->logException(__FUNCTION__, $t);

            return false;
        }

        return true;
    }

    /**
     * Delete.
     *
     * @param string $path
     *
     * @return bool
     */
    public function delete($path)
    {
        $path = $this->applyPathPrefix($path);

        try {
            $this->client->deleteObject($this->bucket, $path);
        } catch (Throwable $t) {
            $this->logException(__FUNCTION__, $t);

            return false;
        }

        return true;
    }

    /**
     * Delete dir.
     *
     * @notice Highly recommend delete dir on Aliyun server.
     * @notice Expect Aliyun officially provide a method.
     *
     * @param string $dirname
     *
     * @return bool
     */
    public function deleteDir($dirname)
    {
        return false;
    }

    /**
     * Create dir. Usually pre-create on Aliyun server.
     *
     * @param string $dirname
     *
     * @return array|bool|false
     */
    public function createDir($dirname, Config $config)
    {
        $path = $this->applyPathPrefix($dirname);

        $options = $this->getOptionsFromConfig($config);

        try {
            $this->client->createObjectDir($this->bucket, $path, $options);
        } catch (Throwable $t) {
            $this->logException(__FUNCTION__, $t);

            return false;
        }

        return true;
    }

    /**
     * Set visibility.
     *
     * @param string $path
     * @param string $visibility
     *
     * @return array|bool|false
     */
    public function setVisibility($path, $visibility)
    {
        $path = $this->applyPathPrefix($path);

        $acl = (AdapterInterface::VISIBILITY_PUBLIC === $visibility) ? OssClient::OSS_ACL_TYPE_PUBLIC_READ : OssClient::OSS_ACL_TYPE_PRIVATE;

        try {
            $this->client->putObjectAcl($this->bucket, $path, $acl);

            return true;
        } catch (Throwable $t) {
            $this->logException(__FUNCTION__, $t);

            return false;
        }
    }

    /**
     * Check exist. Notice, it may throw exception.
     *
     * @param string $path
     *
     * @throws \OSS\Core\OssException
     *
     * @return array|bool|null
     */
    public function has($path)
    {
        $path = $this->applyPathPrefix($path);

        try {
            return $this->client->doesObjectExist($this->bucket, $path);
        } catch (Throwable $t) {
            $this->logException(__FUNCTION__, $t);

            throw new OssException($t->getMessage());
        }
    }

    /**
     * Read.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function read($path)
    {
        $path = $this->applyPathPrefix($path);

        try {
            $contents = $this->client->getObject($this->bucket, $path);

            return compact('contents');
        } catch (Throwable $t) {
            $this->logException(__FUNCTION__, $t);

            return false;
        }
    }

    /**
     * Read stream.
     *
     * @param string $path
     *
     * @return array|false|void
     */
    public function readStream($path)
    {
        try {
            $stream = fopen($this->getUrl($path), 'rb');

            return compact('stream');
        } catch (Throwable $t) {
            return false;
        }
    }

    /**
     * List contents.
     *
     * @notice Expect Aliyun officially provide a method.
     *
     * @param string $directory
     * @param bool   $recursive
     *
     * @return array
     */
    public function listContents($directory = '', $recursive = false)
    {
        return [];
    }

    /**
     * Get metadata.
     *
     * @param string $path
     *
     * @return array|bool|false
     */
    public function getMetadata($path)
    {
        $path = $this->applyPathPrefix($path);

        try {
            return $this->client->getObjectMeta($this->bucket, $path);
        } catch (Throwable $t) {
            $this->logException(__FUNCTION__, $t);

            return false;
        }
    }

    /**
     * Get size.
     *
     * @param string $path
     *
     * @return array|bool|false
     */
    public function getSize($path)
    {
        if ($metadata = $this->getMetadata($path)) {
            $metadata['size'] = $metadata['content-length'];
        }

        return $metadata;
    }

    /**
     * Get mimetype.
     *
     * @param string $path
     *
     * @return array|bool|false
     */
    public function getMimetype($path)
    {
        if ($metadata = $this->getMetadata($path)) {
            $metadata['mimetype'] = $metadata['content-type'];
        }

        return $metadata;
    }

    /**
     * Get timestamp(last modified).
     *
     * @param string $path
     *
     * @return array|bool|false
     */
    public function getTimestamp($path)
    {
        if ($metadata = $this->getMetadata($path)) {
            $metadata['timestamp'] = strtotime($metadata['last-modified']);
        }

        return $metadata;
    }

    /**
     * Get visibility.
     *
     * @param string $path
     *
     * @return array|bool|false
     */
    public function getVisibility($path)
    {
        $path = $this->applyPathPrefix($path);

        try {
            $acl = $this->client->getObjectAcl($this->bucket, $path);

            if (OssClient::OSS_ACL_TYPE_PUBLIC_READ == $acl || OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE == $acl) {
                $acl = AdapterInterface::VISIBILITY_PUBLIC;
            } else {
                $acl = AdapterInterface::VISIBILITY_PRIVATE;
            }

            return ['visibility' => $acl];
        } catch (Throwable $t) {
            $this->logException(__FUNCTION__, $t);

            return false;
        }
    }

    /**
     * For Storage's `url` method.
     *
     * @param $path
     *
     * @return string
     */
    public function getUrl($path)
    {
        $path = $this->applyPathPrefix($path);

        $domain = $this->customDomain ?: ($this->getSchema().$this->bucket.'.'.$this->endPoint);

        return $domain.'/'.$path;
    }

    /**
     * Get schema for url.
     *
     * @return string
     */
    protected function getSchema()
    {
        if ('http' == $this->schema || 'https' == $this->schema) {
            return $this->schema.'://';
        } elseif ('both' == $this->schema) {
            return '//';
        }

        return 'https://';
    }

    /**
     * Get temporary url. For Storage's `temporaryUrl` method.
     * Same with `signUrl` method.
     *
     * @param $path
     * @param $expiration
     * @param $options
     *
     * @return bool|string
     */
    public function getTemporaryUrl($path, $expiration, array $options = [])
    {
        if (! ($expiration = now()->diffInSeconds($expiration))) {
            return false;
        }

        $path = $this->applyPathPrefix($path);

        try {
            $method = isset($options['method']) ?: OssClient::OSS_HTTP_GET;
            $method = strtoupper($method);

            return $this->client->signUrl($this->bucket, $path, $expiration, $method, $options);
        } catch (Throwable $t) {
            $this->logException(__FUNCTION__, $t);

            return false;
        }
    }

    /**
     * Get options for a AliOSS call.
     *
     * @param Config $config
     *
     * @return array aliOSS options
     */
    protected function getOptions(Config $config = null)
    {
        $options = $this->options;

        if ($config) {
            $options = array_merge($options, $this->getOptionsFromConfig($config));
        }

        return [OssClient::OSS_HEADERS => $options];
    }

    /**
     * Retrieve options from a Config instance.
     *
     * @description In face, the commonest config setting is `visibility` and `mimetype`.
     *
     * @return array
     */
    protected function getOptionsFromConfig(Config $config)
    {
        $options = [];

        $metas = array_keys(static::$optionsMap);

        foreach ($metas as $option) {
            if (! $config->has($option)) {
                continue;
            }

            $options[static::$optionsMap[$option]] = $config->get($option);
        }

        if ($visibility = $config->get('visibility')) {
            $options['x-oss-object-acl'] = AdapterInterface::VISIBILITY_PUBLIC === $visibility ? OssClient::OSS_ACL_TYPE_PUBLIC_READ : OssClient::OSS_ACL_TYPE_PRIVATE;
        }

        return $options;
    }

    /**
     * Log exception info for debug.
     *
     * @param string    $fun
     * @param Throwable $t
     */
    protected function logException($fun, $t)
    {
        /* @var Throwable $t*/
        Log::error('Aliyun OSS Error', [
            'function' => $fun,
            'message' => $t->getMessage(),
            'file' => $t->getFile().':'.$t->getLine(),
        ]);
    }

    /**
     * Get oss client. For plugin uses.
     *
     * @return \OSS\OssClient
     */
    public function getOssClient()
    {
        return $this->client;
    }

    /**
     * Get bucket. For plugin uses.
     *
     * @return string
     */
    public function getBucket()
    {
        return $this->bucket;
    }
}
