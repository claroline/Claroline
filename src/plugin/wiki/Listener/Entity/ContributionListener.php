<?php

/*
* This file is part of the Claroline Connect package.
*
* (c) Claroline Consortium <consortium@claroline.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Icap\WikiBundle\Listener\Entity;

use Icap\NotificationBundle\Manager\NotificationManager;
use Icap\WikiBundle\Entity\Contribution;

/**
 * TODO : listen to crud events instead.
 */
class ContributionListener
{
    /** @var NotificationManager */
    private $notificationManager;

    public function __construct(NotificationManager $notificationManager)
    {
        $this->notificationManager = $notificationManager;
    }

    public function postPersist(Contribution $contribution)
    {
        $userPicker = $contribution->getUserPicker();
        $section = $contribution->getSection();
        $wiki = $section->getWiki();
        if (
            null !== $userPicker &&
            count($userPicker->getUserIds()) > 0 &&
            null !== $wiki->getResourceNode()
        ) {
            $details = [
                'contribution' => [
                    'wiki' => $wiki->getId(),
                    'section' => $section->getId(),
                    'id' => $contribution->getId(),
                    'title' => $contribution->getTitle(),
                    'text' => $contribution->getText(),
                    'contributor' => $contribution->getContributor()->getFirstName().
                        ' '.
                        $contribution->getContributor()->getLastName(),
                ],
                'resource' => [
                    'id' => $wiki->getId(),
                    'name' => $wiki->getResourceNode()->getName(),
                    'type' => $wiki->getResourceNode()->getResourceType()->getName(),
                ],
            ];
            $notification = $this->notificationManager->createNotification(
                'resource-icap_wiki-user_tagged',
                'wiki',
                $wiki->getResourceNode()->getId(),
                $details,
                $contribution->getContributor()
            );
            $this->notificationManager->notifyUsers($notification, $userPicker->getUserIds());
        }
    }
}
