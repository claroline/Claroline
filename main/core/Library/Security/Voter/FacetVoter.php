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
use Claroline\CoreBundle\Entity\Facet\PanelFacet;
use Claroline\CoreBundle\Entity\Facet\Facet;
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

    const VIEW = 'view';
    const EDIT = 'edit';

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
            //fields right management is done at the Panel level
            $object = $object->getPanelFacet();
        }

        if ($object instanceof PanelFacet) {
            return $this->panelFacetVote($object, $token, strtolower($attributes[0]));
        } elseif ($object instanceof Facet) {
            //no implementation yet
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    private function panelFacetVote(PanelFacet $panel, TokenInterface $token, $action)
    {
        switch ($action) {
            case self::VIEW: return $this->checkPanelView($token, $panel);
            case self::EDIT: return $this->checkPanelEdit($token, $panel);
        }

        return VoterInterface::ACCESS_DENIED;
    }

    public function checkPanelView(TokenInterface $token, PanelFacet $panel)
    {
        $userRoles = $token->getUser()->getRoles();
        $panelRoles = $panel->getPanelFacetsRole();

        foreach ($panelRoles as $panelRole) {
            if (in_array($panelRole->getRole()->getName(), $userRoles)) {
                if ($panelRole->canOpen()) {
                    return  VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }

    public function checkPanelEdit(TokenInterface $token, PanelFacet $panel)
    {
        $userRoles = $token->getUser()->getRoles();
        $panelRoles = $panel->getPanelFacetsRole();

        foreach ($panelRoles as $panelRole) {
            if (in_array($panelRole->getRole()->getName(), $userRoles)) {
                if ($panelRole->canEdit()) {
                    return  VoterInterface::ACCESS_GRANTED;
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
