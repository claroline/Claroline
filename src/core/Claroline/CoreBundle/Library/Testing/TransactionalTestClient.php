<?php

namespace Claroline\CoreBundle\Library\Testing;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\BrowserKit\History;
use Symfony\Component\BrowserKit\CookieJar;

/**
 * @see http://alexandre-salome.fr/blog/Symfony2-Isolation-Of-Tests
 */
class TransactionalTestClient extends Client
{
    /** @var \Doctrine\DBAL\Connection */
    protected $connection;

    /** @var boolean */
    protected $requested;

    public function __construct(
        HttpKernelInterface $kernel,
        array $server = array(),
        History $history = null,
        CookieJar $cookieJar = null
    )
    {
        parent::__construct($kernel, $server, $history, $cookieJar);
        $this->connection = $this->getContainer()->get('doctrine.dbal.default_connection');
    }

    public function beginTransaction()
    {
        $this->connection->beginTransaction();
    }

    public function rollback()
    {
        $this->connection->rollback();
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function shutdown()
    {
        if ($this->connection->isTransactionActive()) {
            $this->rollback();
        }

        $this->connection->close();
    }

    protected function doRequest($request)
    {
        if ($this->requested) {
            $this->kernel->shutdown();
            $this->kernel->boot();
        }

        $this->injectConnection();
        $this->requested = true;

        return $this->kernel->handle($request);
    }

    protected function injectConnection()
    {
        $this->getContainer()->set('doctrine.dbal.default_connection', $this->connection);
    }
}