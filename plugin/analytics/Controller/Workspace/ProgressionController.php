<?php

namespace Claroline\AnalyticsBundle\Controller\Workspace;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\ProgressionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProgressionController
{
    /** @var ProgressionManager */
    private $progressionManager;

    /**
     * ProgressionController constructor.
     *
     * @param ProgressionManager $progressionManager
     */
    public function __construct(ProgressionManager $progressionManager)
    {
        $this->progressionManager = $progressionManager;
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/progression/{levelMax}",
     *     name="apiv2_progression_items_list"
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "workspace",
     *     class="Claroline\CoreBundle\Entity\Workspace\Workspace",
     *     options={"mapping": {"workspace": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     *
     * @param Workspace $workspace
     * @param int       $levelMax
     * @param User      $user
     *
     * @return JsonResponse
     */
    public function progressionItemsListAction(Workspace $workspace, $levelMax = null, User $user = null)
    {
        $items = $this->progressionManager->fetchItems($workspace, $user, $levelMax);

        return new JsonResponse($items);
    }
}
