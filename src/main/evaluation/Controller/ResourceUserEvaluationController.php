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
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/evaluations/resource")
 */
class ResourceUserEvaluationController
{
    use PermissionCheckerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var FinderProvider */
    private $finder;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        FinderProvider $finder
    ) {
        $this->authorization = $authorization;
        $this->finder = $finder;
    }

    /**
     * @Route("/evaluations/{userEvaluationId}", name="apiv2_workspace_list_resource_evaluations", methods={"GET"})
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
