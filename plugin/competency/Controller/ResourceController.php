<?php

namespace HeVinci\CompetencyBundle\Controller;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Manager\ResourceManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @EXT\Route("/resources", requirements={"id"="\d+"}, options={"expose"=true})
 * @EXT\Method("GET")
 */
class ResourceController
{
    private $resourceManager;

    /**
     * @DI\InjectParams({
     *     "resourceManager" = @DI\Inject("hevinci.competency.resource_manager")
     * })
     *
     * @param ResourceManager $resourceManager
     */
    public function __construct(ResourceManager $resourceManager)
    {
        $this->resourceManager = $resourceManager;
    }

    /**
     * Displays the list of competencies associated with a resource.
     *
     * @EXT\Route("/{id}", name="hevinci_resource_competencies")
     * @SEC\SecureParam(name="resource", permissions="OPEN")
     * @EXT\Template
     *
     * @param ResourceNode $resource
     *
     * @return array
     */
    public function competenciesAction(ResourceNode $resource)
    {
        return [
            '_resource' => $resource,
            'resourceNode' => $resource,
            'workspace' => $resource->getWorkspace(),
            'competencies' => $this->resourceManager->loadLinkedCompetencies($resource),
        ];
    }

    /**
     * Creates an association between a resource and an ability.
     *
     * @EXT\Route("/{id}/abilities/{abilityId}/link", name="hevinci_resource_link_ability")
     * @EXT\Method("POST")
     * @EXT\ParamConverter("ability", options={"id"= "abilityId"})
     * @SEC\SecureParam(name="resource", permissions="OPEN")
     * @SEC\PreAuthorize("hasRole('ROLE_COMPETENCY_MANAGER')")
     *
     * @param ResourceNode $resource
     * @param Ability      $ability
     *
     * @return JsonResponse
     */
    public function linkAbilityAction(ResourceNode $resource, Ability $ability)
    {
        return new JsonResponse(
            $result = $this->resourceManager->createLink($resource, $ability),
            $result ? 200 : 204
        );
    }

    /**
     * Creates an association between a resource and an ability.
     *
     * @EXT\Route("/{id}/competencies/{competencyId}/link", name="hevinci_resource_link_competency")
     * @EXT\Method("POST")
     * @EXT\ParamConverter("competency", options={"id"= "competencyId"})
     * @SEC\SecureParam(name="competency", permissions="OPEN")
     * @SEC\PreAuthorize("hasRole('ROLE_COMPETENCY_MANAGER')")
     *
     * @param ResourceNode $resource
     * @param Competency   $competency
     *
     * @return JsonResponse
     */
    public function linkCompetencyAction(ResourceNode $resource, Competency $competency)
    {
        return new JsonResponse(
            $result = $this->resourceManager->createLink($resource, $competency),
            $result ? 200 : 204
        );
    }

    /**
     * Removes an association between a resource and an ability.
     *
     * @EXT\Route("/{id}/abilities/{abilityId}/remove", name="hevinci_resource_remove_ability")
     * @EXT\ParamConverter("ability", options={"id"= "abilityId"})
     * @SEC\SecureParam(name="resource", permissions="OPEN")
     * @SEC\PreAuthorize("hasRole('ROLE_COMPETENCY_MANAGER')")
     *
     * @param ResourceNode $resource
     * @param Ability      $ability
     *
     * @return JsonResponse
     */
    public function removeAbilityLinkAction(ResourceNode $resource, Ability $ability)
    {
        return new JsonResponse($this->resourceManager->removeLink($resource, $ability));
    }

    /**
     * Removes an association between a resource and a competency.
     *
     * @EXT\Route("/{id}/competencies/{competencyId}/remove", name="hevinci_resource_remove_competency")
     * @EXT\ParamConverter("competency", options={"id"= "competencyId"})
     * @SEC\SecureParam(name="competency", permissions="OPEN")
     * @SEC\PreAuthorize("hasRole('ROLE_COMPETENCY_MANAGER')")
     *
     * @param ResourceNode $resource
     * @param Competency   $competency
     *
     * @return JsonResponse
     */
    public function removeCompetencyLinkAction(ResourceNode $resource, Competency $competency)
    {
        return new JsonResponse($this->resourceManager->removeLink($resource, $competency));
    }

    /**
     * Displays the resources linked to a competency.
     *
     * @EXT\Route("/competencies/{id}", name="hevinci_competency_resources")
     * @SEC\PreAuthorize("hasRole('ROLE_USER')")
     * @EXT\Template
     *
     * @param Competency $competency
     *
     * @return array
     */
    public function competencyResourcesAction(Competency $competency)
    {
        return ['competency' => $competency];
    }

    /**
     * Displays the resources linked to an ability.
     *
     * @EXT\Route("/abilities/{id}", name="hevinci_ability_resources")
     * @SEC\PreAuthorize("hasRole('ROLE_USER')")
     * @EXT\Template
     *
     * @param Ability $ability
     *
     * @return array
     */
    public function abilityResourcesAction(Ability $ability)
    {
        return ['ability' => $ability];
    }
}
