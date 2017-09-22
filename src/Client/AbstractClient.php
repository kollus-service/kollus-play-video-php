<?php

namespace Kollus\Component\Client;

use Kollus\Component\Container;

abstract class AbstractClient
{
    public function __construct(
        $domain = null,
        $version = 0,
        array $optParams = []
    ) {
        $this->initialize($domain, $version, $optParams);
    }

    /**
     * @return void
     */
    protected function __clone()
    {
    }
    /**
     * @return void
     */
    protected function __wakeup()
    {
    }

    /**
     * @var string $domain
     */
    protected $domain;

    /**
     * @var int $version
     */
    protected $version;

    /**
     * @var string $schema
     */
    protected $schema = 'http';

    /**
     * @var array $optParams
     */
    protected $optParams = [];

    /**
     * @var Container\ServiceAccount $serviceAccount
     */
    protected $serviceAccount;

    /**
     * @param string $domain
     * @param int $version
     * @param array $optParams
     */
    protected function initialize($domain, $version, array $optParams)
    {
        $this->domain = $domain;
        $this->version = $version;
        $this->optParams = $optParams;
        if (!isset($this->optParams['timeout'])) {
            $this->optParams['timeout'] = 5;
        }
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @param string $schema
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;
    }

    /**
     * @param Container\ServiceAccount $serviceAccount
     */
    public function setServiceAccount(Container\ServiceAccount $serviceAccount)
    {
        $this->serviceAccount = $serviceAccount;
    }

    /**
     * @return Container\ServiceAccount
     */
    public function getServiceAccount()
    {
        return $this->serviceAccount;
    }

    /**
     * @param mixed|null $client
     * @return self
     */
    abstract public function connect($client = null);

    /**
     * @return self
     */
    abstract public function disconnect();
}
