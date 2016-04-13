<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Security\Voter;

use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * This voter is involved in access decisions for facets.
 *
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class FacetVoter
{
    private $em;
    private $container;

    /**
     * @DI\InjectParams({
     *     "em"        = @DI\Inject("doctrine.orm.entity_manager"),
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct(EntityManager $em, $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    /**
     * Attributes can either be "open" or "edit".
     *
     * @param TokenInterface $token
     * @param $object
     * @param array $attributes
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if ($object instanceof FieldFacet) {
            return $this->fieldFacetVote($object, $token, $attributes[0]);
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    private function fieldFacetVote(FieldFacet $fieldFacet, TokenInterface $token, $attribute)
    {
        $fieldFacetsByRole = $this->container->get('claroline.manager.facet_manager')->getVisibleFieldFacets();
        $canEdit = false;
        $canOpen = false;

        foreach ($fieldFacetsByRole as $field) {
            if ($field['id'] === $fieldFacet->getId()) {
                $canEdit = $field['canEdit'];
                $canOpen = $field['canOpen'];
            }
        }

        if (strtolower($attribute) === 'edit') {
            return $fieldFacet->getIsEditableByOwner() | $canEdit ?
                VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
        }

        if (strtolower($attribute) === 'open') {
            return $fieldFacet->getIsVisibleByOwner() | $canOpen ?
                VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    public function supportsAttribute($attribute)
    {
        return true;
    }

    public function supportsClass($class)
    {
        return true;
    }
}
