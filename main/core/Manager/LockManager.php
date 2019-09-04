<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\ObjectLock;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class LockManager
{
    public function __construct(ObjectManager $om, AuthorizationCheckerInterface $authChecker, TokenStorageInterface $tokenStorage)
    {
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;
        $this->authChecker = $authChecker;
    }

    public function lock($class, $uuid)
    {
        $this->check($class, $uuid);
        $lock = $this->getLock($class, $uuid);
        $lock->setLocked(true);
        $lock->setUser($this->tokenStorage->getToken()->getUser());
        $this->om->persist($lock);
        $this->om->flush();
    }

    public function unlock($class, $uuid)
    {
        $lock = $this->getLock($class, $uuid);
        $lock->setLocked(false);
        $this->om->persist($lock);
        $this->om->flush();
    }

    public function getLock($class, $uuid)
    {
        $lock = $this->om->getRepository(ObjectLock::class)->findOneBy([
          'objectClass' => $class,
          'objectUuid' => $uuid,
        ]);

        if (!$lock) {
            $lock = $this->create($class, $uuid);
        }

        return $lock;
    }

    public function isLocked($class, $uuid)
    {
        $lock = $this->getLock();

        return $lock && $lock->isLocked();
    }

    public function create($class, $uuid)
    {
        $this->check($class, $uuid);
        $lock = new ObjectLock();
        $lock->setObjectUuid($uuid);
        $lock->setObjectClass($class);
        $lock->setUser($this->tokenStorage->getToken()->getUser());
        $this->om->persist($lock);
        $this->om->flush();

        return $lock;
    }

    public function check($class, $uuid)
    {
        $object = $this->om->find($class, $uuid);

        if (!$this->authChecker->isGranted('EDIT', $object)) {
            throw new \Exception('You cannot (un)lock this resource');
        }
    }
}
