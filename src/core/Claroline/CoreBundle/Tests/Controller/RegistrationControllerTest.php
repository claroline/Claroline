<?php

namespace Claroline\CoreBundle\Controller;

use \Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Claroline\CoreBundle\Entity\User;

/**
 * Controller for user self-registration. Access to this functionality requires
 * that the user is anonymous and the self-registration is allowed by the
 * platform configuration.
 */
class RegistrationControllerTest extends MockeryTestCase
{
    private $request;
    private $userManager;
    private $configHandler;
    private $validator;
    private $controller;

    protected function setUp()
    {
        parent::setUp();

        $this->request = $this->mock('Symfony\Component\HttpFoundation\Request');
        $this->userManager = $this->mock('Claroline\CoreBundle\Manager\UserManager');
        $this->configHandler = $this->mock('Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler');
        $this->validator = $this->mock('Symfony\Component\Validator\ValidatorInterface');
        $this->controller = new RegistrationController(
            $this->request,
            $this->userManager,
            $this->configHandler,
            $this->validator
        );
    }

    public function testUnauthorizedPostUserRegistrationAction()
    {
        $this->configHandler->shouldReceive('getParameter')
            ->with('allow_self_registration')->once()->andReturn(false);

        $response = new JsonResponse(array(), 403);
        $this->assertEquals($response->getStatusCode(), $this->controller->postUserRegistrationAction('json')->getStatusCode());
        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\JsonResponse',
            $this->controller->postUserRegistrationAction('json')
        );
    }

    /**
     * @dataProvider postUserProvider
     */
    public function testSuccessfullPostUserRegistrationAction($responseClass, $format, $header)
    {
        $this->configHandler->shouldReceive('getParameter')
            ->with('allow_self_registration')->once()->andReturn(true);

        $bag = $this->getUserParameterBag();
        $this->request->request = $bag;
        $this->validator->shouldReceive('validate')->once()->with(
            m::on(
                function (User $user) {
                    return $user->getPlainPassword() === 'password'
                        && $user->getUsername() === 'username'
                        && $user->getFirstName() === 'firstname'
                        && $user->getLastName() === 'lastname'
                        && $user->getMail() === 'mail@mail.com';
                }
            )
        )->andReturn(array());
        $this->userManager->shouldReceive('createUser')->once()->with(
            m::on(
                function (User $user) {
                    return $user->getPlainPassword() === 'password'
                        && $user->getUsername() === 'username'
                        && $user->getFirstName() === 'firstname'
                        && $user->getLastName() === 'lastname'
                        && $user->getMail() === 'mail@mail.com';
                }
            )
        );

        $response = new $responseClass(array(), 200);
        $this->assertEquals($response->getContent(), $this->controller->postUserRegistrationAction($format)->getContent());
        $this->assertEquals($response->headers->get('content-type'), $header);
    }

    public function testFailedPostUserRegistrationAction()
    {
        $this->configHandler->shouldReceive('getParameter')
            ->with('allow_self_registration')->once()->andReturn(true);

        $bag = $this->getUserParameterBag();
        $this->request->request = $bag;
        $error = $this->mock('Symfony\Component\Validator\ConstraintViolation');
        $error->shouldReceive('getPropertyPath')->once()->andReturn('username');
        $error->shouldReceive('getMessage')->once()->andReturn('message');
        $errorList = array($error);

        $this->validator->shouldReceive('validate')->once()->with(
            m::on(
                function (User $user) {
                    return $user->getPlainPassword() === 'password'
                        && $user->getUsername() === 'username'
                        && $user->getFirstName() === 'firstname'
                        && $user->getLastName() === 'lastname'
                        && $user->getMail() === 'mail@mail.com';
                }
            )
        )->andReturn($errorList);

        $response = new JsonResponse(array(array('property' => 'username', 'message' => 'message')), 422);
        $this->assertEquals($response->getContent(), $this->controller->postUserRegistrationAction('json')->getContent());
        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\JsonResponse',
            $this->controller->postUserRegistrationAction('json')
        );
    }

    public function testUnknownFormatOnRegistration()
    {
        $this->assertEquals(400, $this->controller->postUserRegistrationAction('ABCDEFD')->getStatusCode());
    }

    private function getUserParameterBag()
    {
        $parameterBag = $this->mock('Symfony\Component\HttpFoundation\ServerBag');
        $parameterBag->shouldReceive('get')->with('username')->once()->andReturn('username');
        $parameterBag->shouldReceive('get')->with('password')->once()->andReturn('password');
        $parameterBag->shouldReceive('get')->with('firstName')->once()->andReturn('firstname');
        $parameterBag->shouldReceive('get')->with('lastName')->once()->andReturn('lastname');
        $parameterBag->shouldReceive('get')->with('mail')->once()->andReturn('mail@mail.com');

        return $parameterBag;
    }

    public function postUserProvider()
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
