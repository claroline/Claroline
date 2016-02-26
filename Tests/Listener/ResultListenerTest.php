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

use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\ResultBundle\Entity\Result;
use Symfony\Component\HttpFoundation\Request;

class ResultListenerTest extends TransactionalTestCase
{
    /** @var  ResultListener */
    private $listener;

    protected function setUp()
    {
        parent::setUp();
        $this->listener = $this->client->getContainer()->get('claroline.result.result_listener');
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

    public function testOnWidget()
    {
        $this->listener->setRequest(new Request());
        $this->listener->onDisplayWidget(new DisplayWidgetEvent(new WidgetInstance()));
    }
}
