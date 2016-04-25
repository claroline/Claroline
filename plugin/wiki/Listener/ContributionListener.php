<?php

/*
* This file is part of the Claroline Connect package.
*
* (c) Claroline Consortium <consortium@claroline.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Icap\WikiBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Icap\WikiBundle\Entity\Contribution;
use JMS\DiExtraBundle\Annotation as DI;
use Icap\NotificationBundle\Manager\NotificationManager as NotificationManager;

/**
 * @DI\Service("icap.wiki_bundle.entity_listener.contribution")
 * @DI\Tag("doctrine.entity_listener")
 */
class ContributionListener
{
    /** @var  \Icap\NotificationBundle\Manager\NotificationManager */
    private $notificationManager;

    /**
     * @DI\InjectParams({
     * "notificationManager" = @DI\Inject("icap.notification.manager"),
     * })
     */
    public function __construct(NotificationManager $notificationManager)
    {
        $this->notificationManager = $notificationManager;
    }

    public function postPersist(Contribution $contribution, LifecycleEventArgs $event)
    {
        $userPicker = $contribution->getUserPicker();
        $section = $contribution->getSection();
        $wiki = $section->getWiki();
        if (
            $userPicker !== null &&
            count($userPicker->getUserIds()) > 0 &&
            $wiki->getResourceNode() !== null
        ) {
            $details = array(
                'contribution' => array(
                    'wiki' => $wiki->getId(),
                    'section' => $section->getId(),
                    'id' => $contribution->getId(),
                    'title' => $contribution->getTitle(),
                    'text' => $contribution->getText(),
                    'contributor' => $contribution->getContributor()->getFirstName().
                        ' '.
                        $contribution->getContributor()->getLastName(),
                ),
                'resource' => array(
                    'id' => $wiki->getId(),
                    'name' => $wiki->getResourceNode()->getName(),
                    'type' => $wiki->getResourceNode()->getResourceType()->getName(),
                ),
            );
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
