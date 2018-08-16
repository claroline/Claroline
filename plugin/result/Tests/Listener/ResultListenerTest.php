<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ResultBundle\Listener;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\OpenResourceEvent;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\ResultBundle\Entity\Result;
use Claroline\ResultBundle\Testing\Persister;
use Symfony\Component\HttpFoundation\Request;

class ResultListenerTest extends TransactionalTestCase
{
    /** @var ResultListener */
    private $listener;
    /** @var ObjectManager */
    private $om;
    /** @var Persister */
    private $persist;

    protected function setUp()
    {
        parent::setUp();
        $container = $this->client->getContainer();
        $this->listener = $container->get('claroline.result.result_listener');
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->persist = new Persister($this->om, $container);
    }

    public function testOnOpen()
    {
        $this->listener->setRequest(new Request());
        $this->listener->onOpen(new OpenResourceEvent(new Result()));
    }

    public function testOnDelete()
    {
        $this->listener->setRequest(new Request());
        $this->listener->onDelete(new DeleteResourceEvent(new Result()));
    }
}
