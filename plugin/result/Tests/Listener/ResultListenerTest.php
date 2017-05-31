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

use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Persistence\ObjectManager;
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

    public function testOnCreateForm()
    {
        $event = new CreateFormResourceEvent();
        $this->listener->setRequest(new Request());
        $this->listener->onCreateForm($event);
        $this->assertNotEmpty($event->getResponseContent());
    }

    public function testOnCreate()
    {
        $this->listener->setRequest(new Request());
        $this->listener->onCreate(new CreateResourceEvent());
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
