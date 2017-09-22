<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 4/8/15
 */

namespace Icap\NotificationBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Notification\NotificationUserParametersEvent;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Icap\NotificationBundle\Entity\NotificationUserParameters;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class NotificationUserParametersManager.
 *
 * @DI\Service("icap.notification.manager.notification_user_parameters")
 */
class NotificationUserParametersManager
{
    /**
     * @var \Icap\NotificationBundle\Repository\NotificationUserParametersRepository
     */
    private $notificationUserParametersRepository;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $ed;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @DI\InjectParams({
     *      "em"    = @DI\Inject("claroline.persistence.object_manager"),
     *      "ed"    = @DI\Inject("event_dispatcher")
     * })
     */
    public function __construct(ObjectManager $em, EventDispatcherInterface $ed)
    {
        $this->em = $em;
        $this->ed = $ed;
        $this->notificationUserParametersRepository = $em
            ->getRepository('IcapNotificationBundle:NotificationUserParameters');
    }

    public function getParametersByUser(User $user)
    {
        $parameters = null;
        try {
            $parameters = $this->notificationUserParametersRepository->findParametersByUser($user);
        } catch (\Exception $nre) {
            $parameters = $this->createEmptyParameters($user);
        }

        return $parameters;
    }

    public function getParametersByRssId($rssId)
    {
        return $this->notificationUserParametersRepository->findOneByRssId($rssId);
    }

    public function regenerateRssId($userId)
    {
        $parameters = $this->getParametersByUserId($userId);
        if (!$parameters->isNew()) {
            $parameters->setRssId($this->uniqueRssId());
            $this->em->persist($parameters);
            $this->em->flush();
        }

        return $parameters;
    }

    public function allTypesList(NotificationUserParameters $parameters)
    {
        $allTypes = [];

        $this->ed->dispatch(
            'icap_notification_user_parameters_event',
            new NotificationUserParametersEvent($allTypes)
        );

        $visibleTypes = $parameters->getDisplayEnabledTypes();
        $rssVisibleTypes = $parameters->getRssEnabledTypes();
        foreach ($allTypes as $key => $type) {
            $allTypes[$key]['visible'] = (isset($visibleTypes[$type['name']])) ? $visibleTypes[$type['name']] : true;
            $allTypes[$key]['rssVisible'] = (isset($rssVisibleTypes[$type['name']])) ? $rssVisibleTypes[$type['name']] : false;
        }

        return $allTypes;
    }

    public function processUpdate($newParameters, User $user)
    {
        $userParameters = $this->getParametersByUser($user);
        $allParameterTypes = $this->allTypesList($userParameters);

        $visibleTypes = [];
        $rssVisibleTypes = [];
        foreach ($allParameterTypes as $type) {
            if (isset($newParameters[$type['name']])) {
                $options = $newParameters[$type['name']];
                $visibleTypes[$type['name']] = in_array('visible', $options);
                $rssVisibleTypes[$type['name']] = in_array('rss', $options);
            } else {
                $visibleTypes[$type['name']] = $rssVisibleTypes[$type['name']] = false;
            }
        }
        $userParameters->setDisplayEnabledTypes($visibleTypes);
        $userParameters->setRssEnabledTypes($rssVisibleTypes);
        $this->em->persist($userParameters);
        $this->em->flush();

        return $userParameters;
    }

    private function createEmptyParameters(User $user)
    {
        $parameters = new NotificationUserParameters();
        $parameters->setUser($user);
        $parameters->setRssId($this->uniqueRssId());
        $parameters->setIsNew(true);
        $this->em->persist($parameters);
        $this->em->flush();

        return $parameters;
    }

    private function uniqueRssId()
    {
        return md5(uniqid());
    }
}
