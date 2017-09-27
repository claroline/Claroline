<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Security\Voter;

use Claroline\CoreBundle\Entity\Facet\Facet;
use Claroline\CoreBundle\Entity\Facet\PanelFacet;
use Claroline\CoreBundle\Library\Security\Collection\FieldFacetCollection;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

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
        if ($object instanceof FieldFacetCollection) {
            //fields right management is done at the Panel level
            return $this->fieldFacetVote($object, $token, strtolower($attributes[0]));
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
        $userRoles = array_map(function ($el) {
            return $el->getRole();
        }, $token->getRoles());
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
        $userRoles = array_map(function ($el) {
            return $el->getRole();
        }, $token->getRoles());
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

    public function fieldFacetVote(FieldFacetCollection $collection, TokenInterface $token, $action)
    {
        switch ($action) {
            case self::EDIT: return $this->checkFieldEdit($token, $collection);
        }

        return VoterInterface::ACCESS_DENIED;
    }

    public function checkFieldEdit(TokenInterface $token, $collection)
    {
        foreach ($collection->getFields() as $field) {
            //are we editing ourselves and can we do this ?
            $autoEditAllowed = ($token->getUser() === $collection->getUser()) && $field->getPanelFacet()->isEditable();

            if (!$autoEditAllowed) {
                $panel = $field->getPanelFacet();
                //can we edit the panel because we were granted the right to do it ?
                $access = $this->checkPanelEdit($token, $panel);
                if ($access === VoterInterface::ACCESS_DENIED) {
                    //nope
                    return $access;
                }
            }
        }

        return  VoterInterface::ACCESS_GRANTED;
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
