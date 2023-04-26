<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager\Organization;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;

class OrganizationManager
{
    private ObjectManager $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Check if the user is manager of at least one of the organizations.
     */
    public function isManager(User $user, iterable $organizations): bool
    {
        $adminOrganizations = $user->getAdministratedOrganizations();
        foreach ($adminOrganizations as $adminOrganization) {
            foreach ($organizations as $organization) {
                if ($organization === $adminOrganization) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if the user is member of at least one of the organizations.
     */
    public function isMember(User $user, iterable $organizations): bool
    {
        $userOrganizations = $user->getOrganizations();
        foreach ($userOrganizations as $userOrganization) {
            foreach ($organizations as $organization) {
                if ($organization === $userOrganization) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getDefault($createIfEmpty = false)
    {
        $defaultOrganization = $this->om
            ->getRepository(Organization::class)
            ->findOneBy(['default' => true]);

        if ($createIfEmpty && null === $defaultOrganization) {
            $defaultOrganization = $this->createDefault(true);
        }

        return $defaultOrganization;
    }

    public function createDefault($force = false)
    {
        $default = $this->getDefault();
        if (!$force && $default) {
            return $default;
        }

        $organization = new Organization();
        $organization->setName('default');
        $organization->setCode('default');
        $organization->setDefault(true);
        $organization->setPosition(1);
        $organization->setParent(null);

        $this->om->persist($organization);
        $this->om->flush();

        return $organization;
    }
}
