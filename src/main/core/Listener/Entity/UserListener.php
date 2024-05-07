<?php

namespace Claroline\CoreBundle\Listener\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

/**
 * Manages Life cycle of the User.
 */
class UserListener
{
    public function __construct(
        private readonly EncoderFactory $encoderFactory
    ) {
    }

    /**
     * Encodes the password when a User is persisted.
     */
    public function prePersist(User $user): void
    {
        if (!empty($user->getPlainPassword())) {
            $this->encodePassword($user);
        }
    }

    /**
     * Encodes the password when a User is updated and value has changed.
     */
    public function preUpdate(User $user, PreUpdateEventArgs $event): void
    {
        // UserRepository::upgradePassword() calls setPassword() directly, not setPlainPassword().
        if ($event->hasChangedField('password') && $user->getPlainPassword()) {
            $event->setNewValue('password', $this->encodePassword($user));
        }
    }

    /**
     * Encodes the user password and returns it.
     *
     * @return string - the encoded password
     */
    private function encodePassword(User $user): string
    {
        $password = $this->encoderFactory
            ->getEncoder($user)
            ->encodePassword($user->getPlainPassword(), $user->getSalt());

        $user->setPassword($password);

        return $password;
    }
}
