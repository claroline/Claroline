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

use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Psr\Log\LoggerAwareInterface;

class OrganizationManager implements LoggerAwareInterface
{
    use LoggableTrait;

    /** @var ObjectManager */
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
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

        $this->log('Adding default organization...');

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
