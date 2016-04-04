<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\User;

/**
 * @DI\DoctrineListener(
 *     events = {"prePersist", "preUpdate"},
 *     connection = "default"
 * )
 */
class UserPasswordEncoder implements EventSubscriber
{
    private $encoderFactory;

    /**
     * @DI\InjectParams({
     *     "encoderFactory" = @DI\Inject("security.encoder_factory")
     * })
     */
    public function __construct(EncoderFactory $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    public function getSubscribedEvents()
    {
        return array(Events::prePersist, Events::preUpdate);
    }

    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $user = $eventArgs->getEntity();

        if ($user instanceof User) {
            $password = $this->encodePassword($user);
            $user->setPassword($password);
        }
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $user = $eventArgs->getEntity();

        if ($user instanceof User) {
            if ($eventArgs->hasChangedField('password')) {
                $password = $this->encodePassword($user);
                $eventArgs->setNewValue('password', $password);
                $user->setPassword($password);
            }
        }
    }

    private function encodePassword(User $user)
    {
        return $this->encoderFactory
            ->getEncoder($user)
            ->encodePassword($user->getPlainPassword(), $user->getSalt());
    }
}
