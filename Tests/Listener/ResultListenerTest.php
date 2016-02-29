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
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\ResultBundle\Entity\Result;
use Claroline\ResultBundle\Testing\Persister;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ResultListenerTest extends TransactionalTestCase
{
    /** @var  ResultListener */
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
        $this->persist = new Persister($this->om);
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

    /**
     * @expectedException \LogicException
     */
    public function testOnWidgetThrowsIfNoUser()
    {
        $this->listener->setRequest(new Request());
        $this->listener->onDisplayWidget(new DisplayWidgetEvent(new WidgetInstance()));
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testOnWidgetThrowsIfUserIsNotWorkspaceMember()
    {
        $user = $this->persist->user('bob');
        $this->om->flush();

        /** @var TokenStorage $storage */
        $storage = $this->client->getContainer()->get('security.context');
        $storage->setToken(new UsernamePasswordToken($user, null, 'main'));

        $widget = new WidgetInstance();
        $widget->setWorkspace($user->getPersonalWorkspace());

        $this->listener->setRequest(new Request());
        $this->listener->onDisplayWidget(new DisplayWidgetEvent($widget));
    }
}
