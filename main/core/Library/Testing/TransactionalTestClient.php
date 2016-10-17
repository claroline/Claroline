<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Testing;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\BrowserKit\History;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @see http://alexandre-salome.fr/blog/Symfony2-Isolation-Of-Tests
 */
class TransactionalTestClient extends Client
{
    /** @var \Doctrine\DBAL\Connection */
    protected $connection;

    public function __construct(
        HttpKernelInterface $kernel,
        array $server = [],
        History $history = null,
        CookieJar $cookieJar = null
    ) {
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
        $this->injectConnection();

        return $this->kernel->handle($request);
    }

    protected function injectConnection()
    {
        $this->getContainer()->set('doctrine.dbal.default_connection', $this->connection);
    }
}
