<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\HistoryBundle\Controller;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\HistoryBundle\Manager\HistoryManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * User history.
 *
 * @EXT\Route("/history", options={"expose"=true})
 */
class HistoryController
{
    /** @var SerializerProvider */
    private $serializer;

    /** @var HistoryManager */
    private $manager;

    /**
     * HistoryController constructor.
     *
     * @param SerializerProvider $serializer
     * @param HistoryManager     $manager
     */
    public function __construct(
        SerializerProvider $serializer,
        HistoryManager $manager
    ) {
        $this->serializer = $serializer;
        $this->manager = $manager;
    }

    /**
     * Gets the current user history.
     *
     * @EXT\Route("/", name="claro_user_history")
     * @EXT\ParamConverter("currentUser", converter="current_user")
     *
     * @param User $currentUser
     *
     * @return JsonResponse
     */
    public function listAction(User $currentUser)
    {
        $workspaces = $this->manager->getWorkspaces($currentUser);
        $resources = $this->manager->getResources($currentUser);

        return new JsonResponse([
            'workspaces' => array_map(function (Workspace $workspace) {
                return $this->serializer->serialize($workspace, [Options::SERIALIZE_MINIMAL]);
            }, $workspaces),
            'resources' => array_map(function (ResourceNode $resource) {
                return $this->serializer->serialize($resource, [Options::SERIALIZE_MINIMAL]);
            }, $resources),
        ]);
    }
}
