<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\EvaluationBundle\Controller;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/resource_evaluation")
 */
class ResourceUserEvaluationController
{
    use PermissionCheckerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var FinderProvider */
    private $finder;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        FinderProvider $finder
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->finder = $finder;
    }

    /**
     * @Route("/{nodeId}", name="apiv2_resource_evaluation_list", methods={"GET"})
     * @EXT\ParamConverter("resourceNode", class="Claroline\CoreBundle\Entity\Resource\ResourceNode", options={"mapping": {"nodeId": "uuid"}})
     */
    public function listAction(ResourceNode $resourceNode, Request $request): JsonResponse
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $filters = ['resourceNode' => $resourceNode->getUuid()];
        if (!$this->checkPermission('ADMINISTRATE', $resourceNode)) {
            // only display evaluation of the current user
            /** @var User $user */
            $user = $this->tokenStorage->getToken()->getUser();
            $filters['user'] = $user->getUuid();
        }

        return new JsonResponse(
            $this->finder->search(ResourceUserEvaluation::class, array_merge($request->query->all(), ['hiddenFilters' => $filters]))
        );
    }

    /**
     * @Route("/attempts/{userEvaluationId}", name="apiv2_resource_evaluation_list_attempts", methods={"GET"})
     * @EXT\ParamConverter("userEvaluation", class="Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation", options={"mapping": {"userEvaluationId": "id"}})
     */
    public function listAttemptsAction(ResourceUserEvaluation $userEvaluation, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $userEvaluation, [], true);

        return new JsonResponse(
            $this->finder->search(ResourceEvaluation::class, array_merge($request->query->all(), ['hiddenFilters' => [
                'resourceUserEvaluation' => $userEvaluation,
            ]]))
        );
    }
}
