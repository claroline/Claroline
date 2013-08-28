<?php

namespace Claroline\CoreBundle\Controller;

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
        $this->form = $this->mock('Symfony\Component\Form\Form');
        $this->request = $this->mock('Symfony\Component\HttpFoundation\Request');
        $this->router = $this->mock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');
        $this->formFactory = $this->mock('Claroline\CoreBundle\Form\Factory\FormFactory');
        $this->messageManager = $this->mock('Claroline\CoreBundle\Manager\MessageManager');
        $this->controller = $this->mock(
            'Claroline\CoreBundle\Controller\MessageController[checkAccess]',
            array(
                $this->request,
                $this->router,
                $this->formFactory,
                $this->messageManager
            )
        );
    }

    public function testFormForGroupAction()
    {
        $group = new Group();
        $this->router->shouldReceive('generate')
            ->once()
            ->with('claro_message_show', array('message' => 0))
            ->andReturn('/message');
        $this->messageManager->shouldReceive('generateGroupQueryString')
            ->once()
            ->with($group)
            ->andReturn('?ids[]=1');
        $response = $this->controller->formForGroupAction($group);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('/message?ids[]=1', $response->getTargetUrl());
    }

    public function testSendActionSuccess()
    {
        $sender = new User();
        $message = $this->mock('Claroline\CoreBundle\Entity\Message');
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
        $this->messageManager->shouldReceive('send')
            ->once()
            ->with($sender, $message, $parent)
            ->andReturn($message);
        $message->shouldReceive('getId')->once()->andReturn(1);
        $this->router->shouldReceive('generate')->once()
            ->with('claro_message_show', array('message' => 1))
            ->andReturn('url');

        $response = $this->controller->sendAction($sender, $parent);
        $this->assertEquals('url', $response->getTargetUrl());
    }

    public function testShowOnActionIfMessageExists()
    {
        $user = new User();
        $sender = new User();
        $sender->setUsername('john');
        $message = new Message();
        $message->setSender($sender);
        $message->setObject('Some object...');
        $this->controller->shouldReceive('checkAccess')->once()->with($message, $user);
        $this->messageManager->shouldReceive('markAsRead')->once()->with($user, array($message));
        $this->messageManager->shouldReceive('getConversation')
            ->once()
            ->with($message)
            ->andReturn('ancestors');
        $this->formFactory->shouldReceive('create')
            ->once()
            ->with(FormFactory::TYPE_MESSAGE, array('john', 'Re: Some object...'))
            ->andReturn($this->form);
        $this->form->shouldReceive('createView')->once()->andReturn('form');
        $this->assertEquals(
            array('ancestors' => 'ancestors', 'message' => $message, 'form' => 'form'),
            $this->controller->showAction($user, array(), $message)
        );
    }

    public function testShowOnActionIfMessageIsNull()
    {
        $user = new User();
        $sender = new User();
        $receiverString = 'user1;user2;';
        $this->messageManager->shouldReceive('generateStringTo')->once()
            ->with(array($user, $sender))->andReturn($receiverString);
        $this->formFactory->shouldReceive('create')
            ->once()
            ->with(FormFactory::TYPE_MESSAGE, array($receiverString, ''))
            ->andReturn($this->form);
        $this->form->shouldReceive('createView')->once()->andReturn('form');
        $this->assertEquals(
            array('ancestors' => array(), 'message' => null, 'form' => 'form'),
            $this->controller->showAction($user, array($user, $sender), null)
        );
    }

    /**
     * @dataProvider checkAccessProvider
     */
    public function testCheckAccess($senderName, $username, $receiversString, $isCorrect)
    {
        $controller = new MessageController(
            $this->request,
            $this->router,
            $this->formFactory,
            $this->messageManager
        );

        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $message = $this->mock('Claroline\CoreBundle\Entity\Message');
        $user->shouldReceive('getUsername')->andReturn($username);
        $message->shouldReceive('getSenderUsername')->andReturn($senderName);
        $message->shouldReceive('getTo')->andReturn($receiversString);

        if (!$isCorrect) {
            $this->setExpectedException('\Symfony\Component\Security\Core\Exception\AccessDeniedException');
        }

        $this->assertTrue($controller->checkAccess($message, $user));
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

    public function checkAccessProvider()
    {
        return array(
            array(
                'sendername' => 'toto',
                'username' => 'username',
                'receiversString' => 'user;username;username1',
                'isCorrect' => true
            ),
            array(
                'sendername' => 'username',
                'username' => 'username',
                'receiversString' => 'user;username; username1',
                'isCorrect' => true
            ),
            array(
                'sendername' => 'toto',
                'username' => 'username',
                'receiversString' => 'user; username1;',
                'isCorrect' => false
            )
        );
    }
}
