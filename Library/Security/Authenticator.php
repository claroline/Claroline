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

use Claroline\CoreBundle\Manager\AuthenticationManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * @DI\Service("claroline.authenticator")
 */
class Authenticator
{
    private $sc;
    private $encodeFactory;
    private $authenticationManager;

    /**
     * @DI\InjectParams({
     *     "om"                     = @DI\Inject("claroline.persistence.object_manager"),
     *     "sc"                     = @DI\Inject("security.context"),
     *     "encodeFactory"          = @DI\Inject("security.encoder_factory"),
     *     "authenticationManager"  = @DI\Inject("claroline.common.authentication_manager")
     * })
     */
    public function __construct(
        ObjectManager $om,
        SecurityContextInterface $sc,
        EncoderFactoryInterface $encodeFactory,
        AuthenticationManager $authenticationManager
    )
    {
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->sc = $sc;
        $this->encodeFactory = $encodeFactory;
        $this->authenticationManager = $authenticationManager;
    }

    public function authenticate($username, $password)
    {
        try {
            $user = $this->userRepo->loadUserByUsername($username);
        } catch (\Exception $e) {
            return false;
        }

        $providerKey = 'main';
        $encoder = $this->encodeFactory->getEncoder($user);
        $encodedPass = $encoder->encodePassword($password, $user->getSalt());

        /** external authentication
        throw new \Exception(var_dump($user->getAuthentication()));
        if ($user->getAuthentication() !== '') {
            throw new \Exception(var_dump('test'));
            if ($this->authenticationManager->authenticate($user->getAuthentication(), $username, $password)) {
                $token = new UsernamePasswordToken($user, $password, $providerKey, $user->getRoles());
                $this->sc->setToken($token);

                return true;
            } else {
                return false;
            }
        } **/

        if ($user->getPassword() === $encodedPass) {
            $token = new UsernamePasswordToken($user, $password, $providerKey, $user->getRoles());
            $this->sc->setToken($token);

            return true;
        }

        return false;
    }

}
