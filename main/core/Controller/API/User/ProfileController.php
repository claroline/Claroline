<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\API\User;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use JMS\DiExtraBundle\Annotation as DI;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use Claroline\CoreBundle\Manager\FacetManager;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Put;
use Claroline\CoreBundle\Event\Profile\ProfileLinksEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Claroline\CoreBundle\Library\Security\Collection\FieldFacetCollection;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @NamePrefix("api_")
 */
class ProfileController extends FOSRestController
{
    /**
     * @DI\InjectParams({
     *     "facetManager" = @DI\Inject("claroline.manager.facet_manager"),
     *     "tokenStorage" = @DI\Inject("security.token_storage"),
     *     "request"      = @DI\Inject("request")
     * })
     */
    public function __construct(
        FacetManager $facetManager,
        TokenStorageInterface $tokenStorage,
        Request $request
    ) {
        $this->facetManager = $facetManager;
        $this->tokenStorage = $tokenStorage;
        $this->request = $request;
    }

    /**
     * @Get("/profile/{user}/facets", name="get_profile_facets", options={ "method_prefix" = false })
     * @View(serializerGroups={"api_profile"})
     */
    public function getFacetsAction(User $user)
    {
        $facets = $this->facetManager->getFacetsByUser($user);
        $ffvs = $this->facetManager->getFieldValuesByUser($user);

        foreach ($facets as $facet) {
            foreach ($facet->getPanelFacets() as $panelFacet) {
                if (!$this->isGranted('VIEW', $panelFacet)) {
                    //remove the panel because it's not supposed to be shown
                    $facet->removePanelFacet($panelFacet);
                } else {
                    foreach ($panelFacet->getFieldsFacet() as $field) {
                        foreach ($ffvs as $ffv) {
                            if ($ffv->getFieldFacet()->getId() === $field->getId()) {
                                //for serialization
                            $field->setUserFieldValue($ffv);
                            }
                        }

                        $field->setIsEditable($this->isGranted('EDIT', new FieldFacetCollection([$field], $user)));
                    }
                }
            }
        }

        return $facets;
    }

    /**
     * @Get("/profile/{user}/links", name="get_profile_links", options={ "method_prefix" = false })
     */
    public function getProfileLinksAction(User $user)
    {
        //add check access

        $request = $this->get('request');
        $profileLinksEvent = new ProfileLinksEvent($user, $request->getLocale());
        $this->get('event_dispatcher')->dispatch(
            'profile_link_event',
            $profileLinksEvent
        );

        return $profileLinksEvent->getLinks();
    }

    /**
     * @Put("/profile/{user}/fields", name="put_profile_fields", options={ "method_prefix" = false })
     * @View(serializerGroups={"api_profile"})
     */
    public function putFieldsAction(User $user)
    {
        $fields = $this->request->request->get('fields');

        $entities = array_map(
            function ($el) { return $this->facetManager->getFieldFacet($el['id']); },
            $fields
        );

        $collection = new FieldFacetCollection($entities, $user);

        if (!$this->isGranted('EDIT', $collection)) {
            throw new AccessDeniedException('You do not have the permission to edit these fields.');
        }

        foreach ($fields as $key => $field) {
            $value = isset($field['user_field_value']) ? $field['user_field_value'] : null;
            $this->facetManager->setFieldValue($user, $entities[$key], $value);
        }

        return $user;
    }
}
