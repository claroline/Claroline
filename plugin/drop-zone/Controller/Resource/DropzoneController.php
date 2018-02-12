<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Controller\Resource;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\DropZoneBundle\Entity\Dropzone;
use Claroline\DropZoneBundle\Manager\DropzoneManager;
use Claroline\TeamBundle\Manager\TeamManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @EXT\Route("/dropzone", options={"expose"=true})
 */
class DropzoneController extends Controller
{
    use PermissionCheckerTrait;

    /** @var DropzoneManager */
    private $manager;

    /** @var TeamManager */
    private $teamManager;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * DropzoneController constructor.
     *
     * @DI\InjectParams({
     *     "manager"     = @DI\Inject("claroline.manager.dropzone_manager"),
     *     "teamManager" = @DI\Inject("claroline.manager.team_manager"),
     *     "translator"  = @DI\Inject("translator")
     * })
     *
     * @param DropzoneManager     $manager
     * @param TeamManager         $teamManager
     * @param TranslatorInterface $translator
     */
    public function __construct(
        DropzoneManager $manager,
        TeamManager $teamManager,
        TranslatorInterface $translator
    ) {
        $this->manager = $manager;
        $this->teamManager = $teamManager;
        $this->translator = $translator;
    }

    /**
     * Opens a Dropzone resource.
     *
     * @EXT\Route("/{id}/open", name="claro_dropzone_open")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("dropzone", class="ClarolineDropZoneBundle:Dropzone")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     * @EXT\Template("ClarolineDropZoneBundle:Dropzone:open.html.twig")
     *
     * @param Dropzone $dropzone
     * @param User     $user
     *
     * @return array
     */
    public function openAction(Dropzone $dropzone, User $user = null)
    {
        $resourceNode = $dropzone->getResourceNode();
        $this->checkPermission('OPEN', $resourceNode, [], true);
        $teams = !empty($user) ?
            $this->teamManager->getSearializedTeamsByUserAndWorkspace($user, $resourceNode->getWorkspace()) :
            [];
        $myDrop = null;
        $finishedPeerDrops = [];
        $errorMessage = null;
        $teamId = null;

        if (!$dropzone->getDropClosed() && $dropzone->getAutoCloseDropsAtDropEndDate() && !$dropzone->getManualPlanning()) {
            $dropEndDate = $dropzone->getDropEndDate();

            if ($dropEndDate < new \DateTime()) {
                $this->manager->closeAllUnfinishedDrops($dropzone);
            }
        }

        switch ($dropzone->getDropType()) {
            case Dropzone::DROP_TYPE_USER:
                $myDrop = !empty($user) ? $this->manager->getUserDrop($dropzone, $user) : null;
                $finishedPeerDrops = $this->manager->getFinishedPeerDrops($dropzone, $user);
                break;
            case Dropzone::DROP_TYPE_TEAM:
                $drops = [];
                $teamsIds = array_map(function ($team) {
                    return $team['id'];
                }, $teams);

                /* Fetches team drops associated to user */
                $teamDrops = !empty($user) ? $this->manager->getTeamDrops($dropzone, $user) : [];

                /* Unregisters user from unfinished drops associated to team he doesn't belong to anymore */
                foreach ($teamDrops as $teamDrop) {
                    if (!$teamDrop->isFinished() && !in_array($teamDrop->getTeamId(), $teamsIds)) {
                        /* Unregisters user from unfinished drop */
                        $this->manager->unregisterUserFromTeamDrop($teamDrop, $user);
                    } else {
                        $drops[] = $teamDrop;
                    }
                }
                if (0 === count($drops)) {
                    /* Checks if there are unfinished drops from teams he belongs but not associated to him */
                    $unfinishedTeamsDrops = $this->manager->getTeamsUnfinishedDrops($dropzone, $teamsIds);

                    if (count($unfinishedTeamsDrops) > 0) {
                        $errorMessage = $this->translator->trans('existing_unfinished_team_drop_error', [], 'dropzone');
                    }
                } elseif (1 === count($drops)) {
                    $myDrop = $drops[0];
                } else {
                    $errorMessage = $this->translator->trans('more_than_one_drop_error', [], 'dropzone');
                }
                if (!empty($myDrop)) {
                    $teamId = $myDrop->getTeamId();
                }
                $finishedPeerDrops = $this->manager->getFinishedPeerDrops($dropzone, $user, $teamId);
                break;
        }
        $serializedTools = $this->manager->getSerializedTools();
        /* TODO: generate ResourceUserEvaluation for team */
        $userEvaluation = !empty($user) ? $this->manager->generateResourceUserEvaluation($dropzone, $user) : null;

        return [
            '_resource' => $dropzone,
            'user' => $user,
            'myDrop' => $myDrop,
            'nbCorrections' => count($finishedPeerDrops),
            'tools' => $serializedTools,
            'userEvaluation' => $userEvaluation,
            'teams' => $teams,
            'errorMessage' => $errorMessage,
        ];
    }
}
