<?php

namespace Claroline\CoreBundle\Library\Security;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.authenticator")
 */
class Authenticator
{
    private $sc;
    private $encodeFactory;
    private $userRepo;

    /**
     * @DI\InjectParams({
     *     "om"            = @DI\Inject("claroline.persistence.object_manager"),
     *     "sc"            = @DI\Inject("security.context"),
     *     "encodeFactory" = @DI\Inject("security.encoder_factory")
     * })
     */
    public function __construct(
        ObjectManager $om,
        SecurityContextInterface $sc,
        EncoderFactoryInterface $encodeFactory
    )
    {
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->sc = $sc;
        $this->encodeFactory = $encodeFactory;
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

        if ($user->getPassword() === $encodedPass) {
            $token = new UsernamePasswordToken($user, $password, $providerKey, $user->getRoles());
            $this->sc->setToken($token);

            return true;
        }

        return false;
    }

}
