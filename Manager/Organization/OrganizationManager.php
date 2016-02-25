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

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\BundleRecorder\Log\LoggableTrait;
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
        $this->om->persist($organization);
        $this->om->flush();

        return $organization;
    }

    public function delete(Organization $organization)
    {
        $this->om->remove($organization);
        $this->om->flush();
    }

    public function getAll()
    {
        return $this->repo->findAll();
    }

    public function getRoots()
    {
        return $this->repo->findBy(array('parent' => null));
    }

    public function getDefault()
    {
        return $this->repo->findOneByDefault(true);
    }

    public function createDefault()
    {
        if (count($this->getDefault()) > 0) return;
        $this->log('Adding default organization...');
        $orga = new Organization();
        $orga->setName('default');
        $orga->setDefault(true);
        $orga->setPosition(1);
        $orga->setParent(null);
        $this->om->persist($orga);
        $this->om->flush();
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger()
    {
        return $this->logger;
    }
} 