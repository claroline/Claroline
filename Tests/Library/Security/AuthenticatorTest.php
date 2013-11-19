<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Security;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class AuthenticatorTest extends MockeryTestCase
{
    private $om;
    private $userRepo;
    private $sc;
    private $encoderFactory;
    private $authenticator;

    public function setUp()
    {
        parent::setUp();

        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->userRepo = $this->mock('Claroline\CoreBundle\Repository\UserRepository');
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:User')->andReturn($this->userRepo);
        $this->sc = $this->mock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->encoderFactory = $this->mock('Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface');
        $this->authenticator = new Authenticator($this->om, $this->sc, $this->encoderFactory);
    }

    public function testAuthenticateWithWrongUser()
    {
        $this->userRepo->shouldReceive('loadUserByUsername')->once()->with('name')->andThrow('Exception');
        $this->assertFalse($this->authenticator->authenticate('name', 'pw'));
    }

    public function testAuthenticateWithWrongPassword()
    {
        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $user->shouldReceive('getSalt')->once()->andReturn('salt');
        $user->shouldReceive('getPassword')->once()->andReturn('trueEncodedPw');
        $encoder = $this->mock('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface');
        $this->userRepo->shouldReceive('loadUserByUsername')->once()->with('name')->andReturn($user);
        $this->encoderFactory->shouldReceive('getEncoder')->andReturn($encoder);
        $encoder->shouldReceive('encodePassword')->once()->with('pw', 'salt')->andReturn('WrongEncodedPw');
        $this->assertFalse($this->authenticator->authenticate('name', 'pw'));
    }

    public function testAuthenticateSuccess()
    {
        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $user->shouldReceive('getSalt')->once()->andReturn('salt');
        $user->shouldReceive('getPassword')->once()->andReturn('trueEncodedPw');
        $user->shouldReceive('getRoles')->once()->andReturn(array());
        $encoder = $this->mock('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface');
        $this->userRepo->shouldReceive('loadUserByUsername')->once()->with('name')->andReturn($user);
        $this->encoderFactory->shouldReceive('getEncoder')->andReturn($encoder);
        $encoder->shouldReceive('encodePassword')->once()->with('pw', 'salt')->andReturn('trueEncodedPw');
        $this->sc->shouldReceive('setToken')
            ->with(anInstanceOf('Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken'));
        $this->assertTrue($this->authenticator->authenticate('name', 'pw'));
    }
}
