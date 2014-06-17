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

use Claroline\CoreBundle\Entity\Facet\Facet;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * This voter is involved in access decisions for
 *
 * @DI\Service
 * @DI\Tag("security.voter")
 */

class FacetVoter
{
    private $em;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Attributes can either be "open" or "edit"
     *
     * @param TokenInterface $token
     * @param $object
     * @param array $attributes
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $validAttributes = array('open', 'edit');

        if ($object instanceof FieldFacet) {
            return $this->fieldFacetVote($object, $token, $attributes[0]);
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    private function fieldFacetVote(FieldFacet $fieldFacet, TokenInterface $token, $attribute)
    {
        $fieldFacetsRole = $this->em->getRepository('ClarolineCoreBundle:Facet\FieldFacetRole')
            ->findBy(array('fieldFacet' => $fieldFacet));

        foreach ($fieldFacetsRole as $fieldFacetRole) {
            foreach ($token->getRoles() as $role) {
                if ($fieldFacetRole->getRole()->getName() === $role->getRole()) {

                    if (strtolower($attribute) === 'open' && $fieldFacetRole->canOpen()) {
                        return VoterInterface::ACCESS_GRANTED;
                    }

                    if (strtolower($attribute) === 'edit' && $fieldFacetRole->canEdit()) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                }
            }
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