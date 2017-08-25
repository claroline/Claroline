<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\DataTransformer;

use Claroline\CoreBundle\Manager\Organization\OrganizationManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @Service("claroline.transformer.organization_picker")
 */
class OrganizationPickerTransformer implements DataTransformerInterface
{
    private $organizationManager;
    private $om;

    /**
     * @InjectParams({
     *     "om"                  = @Inject("claroline.persistence.object_manager"),
     *     "organizationManager" = @Inject("claroline.manager.organization.organization_manager")
     * })
     */
    public function __construct(ObjectManager $om, OrganizationManager $organizationManager)
    {
        $this->om = $om;
        $this->organizationManager = $organizationManager;
    }

    public function transform($organizations)
    {
        if (!$organizations) {
            return [];
        }

        if ($organizations instanceof ArrayCollection || $organizations instanceof PersistentCollection) {
            $organizations = $organizations->toArray();
        }

        return array_map(function ($organization) {
            return [
                'id' => $organization->getId(),
                'name' => $organization->getName(),
              ];
        }, $organizations);
    }

    public function reverseTransform($ids)
    {
        return empty($ids) ?
            new ArrayCollection() :
            new ArrayCollection($this->om->findByIds('Claroline\CoreBundle\Entity\Organization\Organization', $ids));
    }
}
