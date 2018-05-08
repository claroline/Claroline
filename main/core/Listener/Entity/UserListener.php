<?php

namespace Claroline\CoreBundle\Listener\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

/**
 * Manages Life cycle of the User.
 *
 * @DI\Service()
 * @DI\Tag("doctrine.entity_listener")
 */
class UserListener
{
    /** @var EncoderFactory */
    private $encoderFactory;

    /**
     * UserListener constructor.
     *
     * @DI\InjectParams({
     *     "encoderFactory" = @DI\Inject("security.encoder_factory")
     * })
     *
     * @param EncoderFactory $encoderFactory
     */
    public function __construct(EncoderFactory $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * Encodes the password when a User is persisted.
     *
     * @param User $user
     */
    public function prePersist(User $user)
    {
        if (!empty($user->getPlainPassword())) {
            $this->encodePassword($user);
        }
    }

    /**
     * Encodes the password when a User is updated and value has changed.
     *
     * @param User               $user
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(User $user, PreUpdateEventArgs $event)
    {
        if ($event->hasChangedField('password')) {
            $event->setNewValue('password', $this->encodePassword($user));
        }
    }

    /**
     * Encodes the user password and returns it.
     *
     * @param User $user
     *
     * @return string - the encoded password
     */
    private function encodePassword(User $user)
    {
        $password = $this->encoderFactory
            ->getEncoder($user)
            ->encodePassword($user->getPlainPassword(), $user->getSalt());

        $user->setPassword($password);

        return $password;
    }
}
