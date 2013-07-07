<?php

namespace Claroline\CoreBundle\Controller;

use \Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Message;

class MessageControllerTest extends MockeryTestCase
{
    private $form;
    private $request;
    private $router;
    private $formFactory;
    private $messageManager;
    private $controller;

    protected function setUp()
    {
        parent::setUp();
        $this->form = m::mock('Symfony\Component\Form\Form');
        $this->request = m::mock('Symfony\Component\HttpFoundation\Request');
        $this->router = m::mock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');
        $this->formFactory = m::mock('Claroline\CoreBundle\Form\Factory\FormFactory');
        $this->messageManager = m::mock('Claroline\CoreBundle\Manager\MessageManager');
        $this->controller = new MessageController(
            $this->request,
            $this->router,
            $this->formFactory,
            $this->messageManager
        );
    }

    public function testFormAction()
    {
        $receivers = array('foo');
        $this->messageManager->shouldReceive('generateStringTo')
            ->once()
            ->with($receivers)
            ->andReturn('foo;');
        $this->formFactory->shouldReceive('create')
            ->once()
            ->with(FormFactory::TYPE_MESSAGE, array('foo;'))
            ->andReturn($this->form);
        $this->form->shouldReceive('createView')->once()->andReturn('view');
        $this->assertEquals(array('form' => 'view'), $this->controller->formAction($receivers));
    }

    public function testFormForGroupAction()
    {
        $group = new Group();
        $this->router->shouldReceive('generate')
            ->once()
            ->with('claro_message_form')
            ->andReturn('/message');
        $this->messageManager->shouldReceive('generateGroupQueryString')
            ->once()
            ->with($group)
            ->andReturn('?ids[]=1');
        $response = $this->controller->formForGroupAction($group);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('/message?ids[]=1', $response->getTargetUrl());
    }

    public function testSendAction()
    {
        $sender = new User();
        $message = new Message();
        $parent = new Message();
        $this->formFactory->shouldReceive('create')
            ->once()
            ->with(FormFactory::TYPE_MESSAGE)
            ->andReturn($this->form);
        $this->form->shouldReceive('handleRequest')
            ->with($this->request)
            ->once()
            ->andReturn(true);
        $this->form->shouldReceive('isValid')->once()->andReturn(true);
        $this->form->shouldReceive('getData')->once()->andReturn($message);
        $this->form->shouldReceive('createView')->once()->andReturn('view');
        $this->messageManager->shouldReceive('send')
            ->once()
            ->with($sender, $message, $parent);
        $this->assertEquals(
            array('form' => 'view'),
            $this->controller->sendAction($sender, $parent)
        );
    }

    public function testShowAction()
    {
        $user = new User();
        $sender = new User();
        $sender->setUsername('john');
        $message = new Message();
        $message->setSender($sender);
        $message->setObject('Some object...');
        $this->messageManager->shouldReceive('markAsRead')->once()->with($user, array($message));
        $this->messageManager->shouldReceive('getConversation')
            ->once()
            ->with($message)
            ->andReturn('ancestors');
        $this->formFactory->shouldReceive('create')
            ->once()
            ->with(FormFactory::TYPE_MESSAGE, array('john', 'Re: Some object...'), $message)
            ->andReturn($this->form);
        $this->form->shouldReceive('createView')->once()->andReturn('form');
        $this->assertEquals(
            array('ancestors' => 'ancestors', 'message' => $message, 'form' => 'form'),
            $this->controller->showAction($user, $message)
        );
    }

    /**
     * @dataProvider messageTypeProvider
     */
    public function testListMessages($type)
    {
        $user = new User();
        $this->messageManager->shouldReceive("get{$type}Messages")
            ->once()
            ->with($user, 'someSearch', 1)
            ->andReturn('msgPager');
        $this->assertEquals(
            array('pager' => 'msgPager', 'search' => 'someSearch'),
            $this->controller->{"list{$type}Action"}($user, 1, 'someSearch')
        );
    }

    /**
     * @dataProvider messageActionProvider
     */
    public function testActOnMessages($controllerAction, $managerMethod)
    {
        $user = new User();
        $messages = array('foo');
        $this->messageManager->shouldReceive($managerMethod)
            ->once()
            ->with($user, $messages);
        $response = $this->controller->{"{$controllerAction}Action"}($user, $messages);
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testMarkAsReadAction()
    {
        $user = new User();
        $message = new Message();
        $this->messageManager->shouldReceive('markAsRead')
            ->once()
            ->with($user, array($message));
        $response = $this->controller->markAsReadAction($user, $message);
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function messageTypeProvider()
    {
        return array(
            array('Received'),
            array('Sent'),
            array('Removed')
        );
    }

    public function messageActionProvider()
    {
        return array(
            array('restoreFromTrash', 'markAsUnremoved'),
            array('softDelete', 'markAsRemoved'),
            array('delete', 'remove')
        );
    }
}