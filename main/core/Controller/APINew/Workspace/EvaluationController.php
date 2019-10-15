<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Workspace;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/workspace")
 */
class EvaluationController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var FinderProvider */
    private $finder;
    /** @var SerializerProvider */
    private $serializer;

    /**
     * EvaluationController constructor.
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param FinderProvider                $finder
     * @param SerializerProvider            $serializer
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        FinderProvider $finder,
        SerializerProvider $serializer
    ) {
        $this->authorization = $authorization;
        $this->finder = $finder;
        $this->serializer = $serializer;
    }

    /**
     * @Route(
     *    "/{workspace}/evaluations/list",
     *    name="apiv2_workspace_evaluations_list"
     * )
     * @Method("GET")
     * @ParamConverter("workspace", options={"mapping": {"workspace": "uuid"}})
     *
     * @param Workspace $workspace
     * @param Request   $request
     *
     * @return JsonResponse
     */
    public function evaluationsListAction(Workspace $workspace, Request $request)
    {
        if (!$this->authorization->isGranted(['dashboard', 'OPEN'], $workspace)) {
            throw new AccessDeniedException();
        }

        return new JsonResponse($this->finder->search(
            Evaluation::class,
            array_merge($request->query->all(), ['hiddenFilters' => [
                'workspace' => $workspace->getUuid(),
            ]])
        ));
    }
}
