<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HeVinci\CompetencyBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Security\Collection\ResourceCollection;
use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Manager\CompetencyManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/ability")
 */
class AbilityController extends AbstractCrudController
{
    /** @var AuthorizationCheckerInterface */
    protected $authorization;

    /** @var CompetencyManager */
    private $manager;

    public function __construct(AuthorizationCheckerInterface $authorization, CompetencyManager $manager)
    {
        $this->authorization = $authorization;
        $this->manager = $manager;
    }

    public function getName(): string
    {
        return 'ability';
    }

    public function getClass(): string
    {
        return Ability::class;
    }

    public function getIgnore(): array
    {
        return ['create', 'update', 'deleteBulk', 'get'];
    }

    /**
     * @Route(
     *     "/node/{node}/abilities/fetch",
     *     name="apiv2_competency_resource_abilities_list"
     * )
     *
     * @EXT\ParamConverter(
     *     "node",
     *     class="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     options={"mapping": {"node": "uuid"}}
     * )
     */
    public function resourceAbilitiesFetchAction(ResourceNode $node): JsonResponse
    {
        $abilities = $this->crud->list(
            Ability::class,
            ['resources' => [$node->getUuid()]]
        );

        return new JsonResponse($abilities);
    }

    /**
     * @Route(
     *     "/node/{node}/ability/{ability}/associate",
     *     name="apiv2_competency_resource_ability_associate",
     *     methods={"POST"}
     * )
     *
     * @EXT\ParamConverter(
     *     "node",
     *     class="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     options={"mapping": {"node": "uuid"}}
     * )
     * @EXT\ParamConverter(
     *     "ability",
     *     class="HeVinci\CompetencyBundle\Entity\Ability",
     *     options={"mapping": {"ability": "uuid"}},
     * )
     *
     * @return JsonResponse
     */
    public function resourceAbilityAssociateAction(ResourceNode $node, Ability $ability)
    {
        $this->checkResourceAccess($node, 'EDIT');

        $associatedNodes = $this->manager->associateAbilityToResources($ability, [$node]);
        $data = 0 < count($associatedNodes) ?
            $this->serializer->serialize($ability) :
            null;

        return new JsonResponse($data);
    }

    /**
     * @Route(
     *     "/node/{node}/ability/{ability}/dissociate",
     *     name="apiv2_competency_resource_ability_dissociate",
     *     methods={"DELETE"}
     * )
     *
     * @EXT\ParamConverter(
     *     "node",
     *     class="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     options={"mapping": {"node": "uuid"}}
     * )
     * @EXT\ParamConverter(
     *     "ability",
     *     class="HeVinci\CompetencyBundle\Entity\Ability",
     *     options={"mapping": {"ability": "uuid"}},
     * )
     *
     * @return JsonResponse
     */
    public function resourceAbilityDissociateAction(ResourceNode $node, Ability $ability)
    {
        $this->checkResourceAccess($node, 'EDIT');

        $this->manager->dissociateAbilityFromResources($ability, [$node]);

        return new JsonResponse();
    }

    /**
     * @param string $rights
     */
    private function checkResourceAccess(ResourceNode $node, $rights = 'OPEN')
    {
        $collection = new ResourceCollection([$node]);

        if (!$this->authorization->isGranted($rights, $collection)) {
            throw new AccessDeniedException();
        }
    }
}
