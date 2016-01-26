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

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class TransactionalTestCase extends WebTestCase
{
    /** @var TransactionalTestClient */
    protected $client;

    protected function setUp()
    {
        parent::setUp();
        $this->client = self::createClient();
        $this->client->beginTransaction();
    }

    protected function tearDown()
    {
        // we can't simply do "$client->shutdown()" because sometimes
        // when an integration test fails (e.g. due to an error in the
        // container configuration) the $client property is set to null
        // (by PHPUnit?) and the original error is hidden behind a fatal
        // "Call to a member function shutdown() on a non-object"...
        if ($this->client instanceof TransactionalTestClient) {
            $this->client->shutdown();
        }

        parent::tearDown();
    }
}
