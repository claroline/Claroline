<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BigBlueButtonBundle\Manager;

use Claroline\BigBlueButtonBundle\Entity\BBB;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @DI\Service("claroline.manager.bbb_manager")
 */
class BBBManager
{
    private $authorization;
    private $om;
    private $bbbRepo;

    /**
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "om"            = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->bbbRepo = $om->getRepository('Claroline\BigBlueButtonBundle\Entity\BBB');
    }

    public function updateBBB(
        BBB $bbb,
        $roomName = null,
        $welcomeMessage = null,
        $newTab = false,
        $moderatorRequired = false,
        $record = false,
        $startDate = null,
        $endDate = null
    ) {
        $bbb->setRoomName($roomName);
        $bbb->setWelcomeMessage($welcomeMessage);
        $bbb->setNewTab($newTab);
        $bbb->setModeratorRequired($moderatorRequired);
        $bbb->setRecord($record);
        $bbb->setStartDate($startDate);
        $bbb->setEndDate($endDate);
        $this->om->persist($bbb);
        $this->om->flush();
    }

    public function getBBBWithDatesByWorkspace(Workspace $workspace)
    {
        return $this->bbbRepo->findBBBWithDatesByWorkspace($workspace);
    }

    /******************
     * Rights methods *
     ******************/

    public function checkRight(BBB $bbb, $right)
    {
        $collection = new ResourceCollection([$bbb->getResourceNode()]);

        if (!$this->authorization->isGranted($right, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }

    public function hasRight(BBB $bbb, $right)
    {
        $collection = new ResourceCollection([$bbb->getResourceNode()]);

        return $this->authorization->isGranted($right, $collection);
    }
}
