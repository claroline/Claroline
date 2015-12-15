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

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Organization\Organization;
/**
 * @DI\Service("claroline.manager.organization_manager")
 */
class OrganizationManager
{
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
} 