<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Listener\Rules;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\PatchEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Claroline\OpenBadgeBundle\Manager\RuleManager;

class RoleListener
{
    /** @var ObjectManager */
    private $om;
    /** @var RuleManager */
    private $manager;

    public function __construct(
        ObjectManager $om,
        RuleManager $manager
    ) {
        $this->om = $om;
        $this->manager = $manager;
    }

    public function onUserPatch(PatchEvent $event)
    {
        if (Crud::COLLECTION_ADD === $event->getAction()) {
            $user = $event->getObject();

            $roles = [];
            if ($event->getValue() instanceof Role) {
                $roles[] = $event->getValue();
            } elseif ($event->getValue() instanceof Group) {
                // gets all the roles the user inherits from the new group
                foreach ($event->getValue()->getEntityRoles() as $role) {
                    if (!$user->hasRole($role->getName(), false)) {
                        $roles[] = $role;
                    }
                }
            }

            foreach ($roles as $role) {
                /** @var Rule[] $rules */
                $rules = $this->om->getRepository(Rule::class)->findBy(['role' => $role]);

                foreach ($rules as $rule) {
                    $this->manager->grant($rule, $user);
                }
            }
        }
    }

    public function onRolePatch(PatchEvent $event)
    {
        if (Crud::COLLECTION_ADD === $event->getAction()) {
            $role = $event->getObject();

            /** @var Rule[] $rules */
            $rules = $this->om->getRepository(Rule::class)->findBy(['role' => $role]);
            if (!empty($rules)) {
                $users = [];
                if ($event->getValue() instanceof User) {
                    $users[] = $event->getValue();
                } elseif ($event->getValue() instanceof Group) {
                    foreach ($event->getValue()->getUsers() as $user) {
                        if ($user->isEnabled() && !$user->isRemoved() && !$user->hasRole($role->getName(), false)) {
                            $users[] = $user;
                        }
                    }
                }

                foreach ($rules as $rule) {
                    foreach ($users as $user) {
                        $this->manager->grant($rule, $user);
                    }
                }
            }
        }
    }

    public function onGroupPatch(PatchEvent $event)
    {
        if (Crud::COLLECTION_ADD === $event->getAction()) {
            $group = $event->getObject();

            if ($event->getValue() instanceof User) {
                $user = $event->getValue();

                $roles = [];
                foreach ($group->getEntityRoles() as $groupRole) {
                    if (!$event->getValue()->hasRole($groupRole->getName(), false)) {
                        $roles[] = $groupRole;
                    }
                }

                foreach ($roles as $role) {
                    /** @var Rule[] $rules */
                    $rules = $this->om->getRepository(Rule::class)->findBy(['role' => $role]);
                    foreach ($rules as $rule) {
                        $this->manager->grant($rule, $user);
                    }
                }
            } elseif ($event->getValue() instanceof Role) {
                $role = $event->getValue();

                $users = [];
                foreach ($group->getUsers() as $user) {
                    if ($user->isEnabled() && !$user->isRemoved() && !$user->hasRole($role->getName(), false)) {
                        $users[] = $user;
                    }
                }

                /** @var Rule[] $rules */
                $rules = $this->om->getRepository(Rule::class)->findBy(['role' => $role]);
                foreach ($rules as $rule) {
                    foreach ($users as $user) {
                        $this->manager->grant($rule, $user);
                    }
                }
            }
        }
    }
}
