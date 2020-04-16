<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AuthenticationBundle\Security\Authentication;

use Claroline\AppBundle\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class Authenticator
{
    /** @var EncoderFactoryInterface */
    private $encodeFactory;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var UserProviderInterface */
    private $userRepo;

    /**
     * Authenticator constructor.
     *
     * @param ObjectManager           $om
     * @param TokenStorageInterface   $tokenStorage
     * @param EncoderFactoryInterface $encodeFactory
     */
    public function __construct(
        ObjectManager $om,
        TokenStorageInterface $tokenStorage,
        EncoderFactoryInterface $encodeFactory
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->encodeFactory = $encodeFactory;

        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
    }

    public function authenticate($username, $password, $validatePassword = true)
    {
        try {
            /** @var UserInterface $user */
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
