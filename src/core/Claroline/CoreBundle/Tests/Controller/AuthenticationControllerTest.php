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
        $this->controller = new AuthenticationController(
            $this->request,
            $this->userManager,
            $this->encoderFactory,
            $this->om,
            $this->mailer,
            $this->router,
            $this->translator,
            $this->formFactory
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
        $this->mailer->shouldReceive('send')->once()->with(
            m::on(
                function ($message) {
                    return $message->getSubject() === 'reset'
                        && $message->getFrom() === array('noreply@claroline.net' => null)
                        && $message->getTo() === array('toto@claroline.com' => null)
                        && $message->getBody() === '<p><a href="http://jorgeaimejquery/reset/123"/>blabla</a></p>';
                }
            )
        );
        $form->shouldReceive('createView')
            ->once()
            ->andReturn('view');
        $this->assertEquals(array('user' => $user, 'form' => 'view'), $this->controller->sendEmailAction());
    }
}
