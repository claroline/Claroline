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

use Claroline\AppBundle\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class Authenticator
{
    private $sc;
    private $encodeFactory;
    private $tokenStorage;

    public function __construct(
        ObjectManager $om,
        TokenStorageInterface $tokenStorage,
        EncoderFactoryInterface $encodeFactory
    ) {
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->tokenStorage = $tokenStorage;
        $this->encodeFactory = $encodeFactory;
    }

    public function authenticate($username, $password, $validatePassword = true)
    {
        try {
            $user = $this->userRepo->loadUserByUsername($username);
        } catch (\Exception $e) {
            return false;
        }

        $providerKey = 'main';

        if ($validatePassword) {
            $encoder = $this->encodeFactory->getEncoder($user);
            $encodedPass = $encoder->encodePassword($password, $user->getSalt());

            if ($user->getPassword() === $encodedPass) {
                $token = new UsernamePasswordToken($user, $password, $providerKey, $user->getRoles());
                $this->tokenStorage->setToken($token);

                return true;
            }

            return false;
        }

        $token = new UsernamePasswordToken($user, $password, $providerKey, $user->getRoles());
        $this->tokenStorage->setToken($token);

        return true;
    }
}
