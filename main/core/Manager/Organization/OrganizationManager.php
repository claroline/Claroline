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

use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerInterface;

/**
 * @DI\Service("claroline.manager.organization.organization_manager")
 */
class OrganizationManager
{
    use LoggableTrait;

    private $om;

    /**
     * @DI\InjectParams({
     *       "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->repo = $om->getRepository('ClarolineCoreBundle:Organization\Organization');
    }

    public function create(Organization $organization)
    {
        $this->om->persist($organization);
        $this->om->flush();

        return $organization;
    }

    public function edit(Organization $organization)
    {
        //if I inverse the mappedBy and inversedBy doctrine mapping, I don't need to do this.
        //but it it's done, the search request will break.
        //no idea why
        foreach ($organization->getAdministrators() as $administrator) {
            $administrator->addOrganization($organization);
            $this->om->persist($administrator);
        }

        $this->om->persist($organization);
        $this->om->flush();

        return $organization;
    }

    public function delete(Organization $organization)
    {
        if ($organization->isDefault()) {
            throw new \Exception('Default organization can not be removed');
        }

        $this->om->remove($organization);
        $this->om->flush();
    }

    public function getAll()
    {
        return $this->repo->findAll();
    }

    public function getRoots()
    {
        return $this->repo->findBy(['parent' => null]);
    }

    public function getDefault($createIfEmpty = false)
    {
        $defaultOrganization = $this->repo->findOneByDefault(true);
        if ($createIfEmpty && $defaultOrganization === null) {
            $defaultOrganization = $this->createDefault(true);
        }

        return $defaultOrganization;
    }

    public function createDefault($force = false)
    {
        if (!$force && count($this->getDefault()) > 0) {
            return;
        }
        $this->log('Adding default organization...');
        $orga = new Organization();
        $orga->setName('default');
        $orga->setDefault(true);
        $orga->setPosition(1);
        $orga->setParent(null);
        $this->om->persist($orga);
        $this->om->flush();

        return $orga;
    }

    public function setParent(Organization $organization, Organization $parent = null)
    {
        $organization->setParent($parent);
        $this->om->persist($organization);
        $this->om->flush();

        return $organization;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function getOrganizationsByIds(array $ids)
    {
        return $this->repo->findOrganizationsByIds($ids);
    }
}
