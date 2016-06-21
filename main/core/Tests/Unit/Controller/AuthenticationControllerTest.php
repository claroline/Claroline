<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Form\Factory\FormFactory;

class AuthenticationControllerTest extends MockeryTestCase
{
    private $request;
    private $userManager;
    private $encoderFactory;
    private $om;
    private $mailManager;
    private $translator;
    private $formFactory;
    private $authenticator;
    private $controller;
    private $router;

    protected function setUp()
    {
        parent::setUp();

        $this->request = $this->mock('Symfony\Component\HttpFoundation\Request');
        $this->userManager = $this->mock('Claroline\CoreBundle\Manager\UserManager');
        $this->encoderFactory = $this->mock('Symfony\Component\Security\Core\Encoder\EncoderFactory');
        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->mailManager = $this->mock('Claroline\CoreBundle\Manager\MailManager');
        $this->translator = $this->mock('Symfony\Component\Translation\Translator');
        $this->formFactory = $this->mock('Claroline\CoreBundle\Form\Factory\FormFactory');
        $this->authenticator = $this->mock('Claroline\CoreBundle\Library\Security\Authenticator');
        $this->router = $this->mock('Symfony\Component\Routing\RouterInterface');

        $this->controller = new AuthenticationController(
            $this->request,
            $this->userManager,
            $this->encoderFactory,
            $this->om,
            $this->translator,
            $this->formFactory,
            $this->authenticator,
            $this->mailManager,
            $this->router
        );
    }

    public function testSendEmailAction()
    {
        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $form = $this->mock('Symfony\Component\Form\Form');

        $this->formFactory->shouldReceive('create')
            ->once()
            ->with(FormFactory::TYPE_USER_EMAIL, array(), null)
            ->andReturn($form);
        $form->shouldReceive('handleRequest')
            ->with($this->request)
            ->once();
        $form->shouldReceive('isValid')
            ->once()
            ->andReturn(true);
        $form->shouldReceive('getData')
            ->once()
            ->andReturn(array('mail' => 'toto@claroline.com'));
        $this->userManager->shouldReceive('getUserByEmail')
            ->once()
            ->with('toto@claroline.com')
            ->andReturn($user);
        $user->shouldReceive('getUsername')->once()->andReturn('toto');
        $user->shouldReceive('getSalt')->once()->andReturn('fsdf');
        $user->shouldReceive('setHashTime')->once()->with(intValue());
        $user->shouldReceive('setResetPasswordHash')->once()->with(stringValue());
        $user->shouldReceive('getResetPasswordHash')->once()->andReturn('123');
        $this->om->shouldReceive('persist')->once()->with($user);
        $this->om->shouldReceive('flush')->once();
        $this->mailManager
            ->shouldReceive('sendForgotPassword')
            ->once()
            ->with($user)
            ->andReturn(true);
        $form->shouldReceive('createView')
            ->once()
            ->andReturn('view');

        $this->assertEquals(
            array('user' => $user, 'form' => 'view'),
            $this->controller->sendEmailAction()
        );
    }

    public function testPostAuthenticationAction()
    {
        $parameterBag = $this->mock('Symfony\Component\HttpFoundation\ServerBag');
        $parameterBag->shouldReceive('get')->with('username')->once()->andReturn('username');
        $parameterBag->shouldReceive('get')->with('password')->once()->andReturn('password');
        $this->request->request = $parameterBag;
        $this->authenticator->shouldReceive('authenticate')->once()->with('username', 'password')
            ->andReturn(true);
        $response = $this->controller->postAuthenticationAction('json');
        $this->assertEquals('[]', $response->getContent());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
    }

    /**
     * @dataProvider postAuthenticationProvider
     */
    public function testFailedPostAuthenticationAction($responseClass, $format, $header)
    {
        $parameterBag = $this->mock('Symfony\Component\HttpFoundation\ServerBag');
        $parameterBag->shouldReceive('get')->with('username')->once()->andReturn('username');
        $parameterBag->shouldReceive('get')->with('password')->once()->andReturn('password');
        $this->request->request = $parameterBag;
        $this->authenticator->shouldReceive('authenticate')->once()->with('username', 'password')
            ->andReturn(false);
        $this->translator->shouldReceive('trans')->once()->with('login_failure', array(), 'platform')
            ->andReturn('message');
        $response = new $responseClass(array('message' => 'message'), 403);
        $this->assertEquals(
            $response->getContent(),
            $this->controller->postAuthenticationAction($format)->getContent()
        );
        $this->assertEquals($response->headers->get('content-type'), $header);
    }

    public function testUnknownFormatOnAuthentication()
    {
        $this->assertEquals(400, $this->controller->postAuthenticationAction('ABCDEFD')->getStatusCode());
    }

    public function postAuthenticationProvider()
    {
        return array(
            array(
                'responseClass' => '\Claroline\CoreBundle\Library\HttpFoundation\XmlResponse',
                'format' => 'xml',
                'header' => 'text/xml',
            ),
            array(
                'responseClass' => '\Symfony\Component\HttpFoundation\JsonResponse',
                'format' => 'json',
                'header' => 'application/json',
           ),
        );
    }
}
