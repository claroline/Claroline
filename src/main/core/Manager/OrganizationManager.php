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
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;

class OrganizationManager
{
    public function __construct(
        private readonly ObjectManager $om
    ) {
    }

    /**
     * Check if the user is a manager of at least one of the organizations.
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
     * Check if the user is a member of at least one of the organizations.
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

    public function setDefault(Organization $organization): void
    {
        // remove default flag from previous default
        $default = $this->getDefault(false);
        $default->setDefault(false);
        $this->om->persist($default);

        // set organization as default
        $organization->setDefault(true);
        $this->om->persist($organization);

        $this->om->flush();
    }

    public function getDefault(?bool $createIfEmpty = false): ?Organization
    {
        $defaultOrganization = $this->om
            ->getRepository(Organization::class)
            ->findOneBy(['default' => true]);

        if ($createIfEmpty && null === $defaultOrganization) {
            $defaultOrganization = $this->createDefault();
        }

        return $defaultOrganization;
    }

    private function createDefault(): Organization
    {
        $organization = new Organization();
        $organization->setName('default');
        $organization->setCode('default');
        $organization->setDefault(true);

        $this->om->persist($organization);
        $this->om->flush();

        return $organization;
    }
}
