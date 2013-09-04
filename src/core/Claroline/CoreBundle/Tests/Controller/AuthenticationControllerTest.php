<?php
namespace Claroline\CoreBundle\Controller;

use \Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Form\Factory\FormFactory;

class AuthenticationControllerTest extends MockeryTestCase
{
    private $request;
    private $router;
    private $userManager;
    private $encoderFactory;
    private $om;
    private $mailer;
    private $translator;
    private $formFactory;
    private $authenticator;
    private $controller;
    private $templating;

    protected function setUp()
    {
        parent::setUp();

        $this->request = $this->mock('Symfony\Component\HttpFoundation\Request');
        $this->router = $this->mock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');
        $this->userManager = $this->mock('Claroline\CoreBundle\Manager\UserManager');
        $this->encoderFactory = $this->mock('Symfony\Component\Security\Core\Encoder\EncoderFactory');
        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->mailer = $this->mock('Swift_Mailer');
        $this->translator = $this->mock('Symfony\Component\Translation\Translator');
        $this->formFactory = $this->mock('Claroline\CoreBundle\Form\Factory\FormFactory');
        $this->authenticator = $this->mock('Claroline\CoreBundle\Library\Security\Authenticator');
        $this->templating = $this->mock('Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine');
        $this->controller = new AuthenticationController(
            $this->request,
            $this->userManager,
            $this->encoderFactory,
            $this->om,
            $this->mailer,
            $this->router,
            $this->translator,
            $this->formFactory,
            $this->authenticator,
            $this->templating
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
        $parameterBag = $this->mock('Symfony\Component\HttpFoundation\ServerBag');
        $parameterBag->shouldReceive('get')
            ->once()
            ->with('HTTP_ORIGIN')
            ->andReturn('http://jorgeaimejquery');
        $this->request->server = $parameterBag;
        $this->om->shouldReceive('persist')->once()->with($user);
        $this->om->shouldReceive('flush')->once();
        $this->router->shouldReceive('generate')
            ->once()
            ->with('claro_security_reset_password', array('hash' => '123'))
            ->andReturn('/reset/123');
        $this->translator->shouldReceive('trans')->once()->with('mail_click', array(), 'platform')->andReturn('blabla');
        $this->translator->shouldReceive('trans')->once()->with('reset_pwd', array(), 'platform')->andReturn('reset');
        $this->templating->shouldReceive('render')->once()
            ->with(
                'ClarolineCoreBundle:Authentication:emailForgotPassword.html.twig',
                array('message' => 'blabla', 'link' => 'http://jorgeaimejquery/reset/123')
            )
            ->andReturn('<html><body> <p> <a href="http://jorgeaimejquery/reset/123"/>blabla</a> </p></body></html>');
        $this->mailer->shouldReceive('send')->once()->with(
            m::on(
                function ($message) {

                    return $message->getSubject() === 'reset'
                        && $message->getFrom() === array('noreply@claroline.net' => null)
                        && $message->getTo() === array('toto@claroline.com' => null)
                        && $message->getBody() === '<html><body> <p> <a href="http://jorgeaimejquery/reset/123"/>blabla</a> </p></body></html>';
                }
            )
        );
        $form->shouldReceive('createView')
            ->once()
            ->andReturn('view');
        $this->assertEquals(array('user' => $user, 'form' => 'view'), $this->controller->sendEmailAction());
    }

    public function testPostAuthenticationAction()
    {
        $parameterBag = $this->mock('Symfony\Component\HttpFoundation\ServerBag');
        $parameterBag->shouldReceive('get')->with('username')->once()->andReturn('username');
        $parameterBag->shouldReceive('get')->with('password')->once()->andReturn('password');
        $this->request->request = $parameterBag;
        $this->authenticator->shouldReceive('authenticate')->once()->with('username', 'password')
            ->andReturn(true);
        $response = new \Symfony\Component\HttpFoundation\JsonResponse(array(), 200);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
        $this->assertEquals($response->getContent(), $this->controller->postAuthenticationAction('json')->getContent());
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
        $this->assertEquals($response->getContent(), $this->controller->postAuthenticationAction($format)->getContent());
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
                'header' => 'text/xml'
            ),
            array(
                'responseClass' => '\Symfony\Component\HttpFoundation\JsonResponse',
                'format' => 'json',
                'header' => 'application/json'
           )
        );
    }
}
