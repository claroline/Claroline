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

use Claroline\CoreBundle\Event\Notification\NotificationUserParametersEvent;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
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
     *      "em"    = @DI\Inject("doctrine.orm.entity_manager"),
     *      "ed"    = @DI\Inject("event_dispatcher")
     * })
     */
    public function __construct(EntityManager $em, EventDispatcherInterface $ed)
    {
        $this->em = $em;
        $this->ed = $ed;
        $this->notificationUserParametersRepository = $em
            ->getRepository('IcapNotificationBundle:NotificationUserParameters');
    }

    public function getParametersByUserId($userId)
    {
        $parameters = null;
        try {
            $parameters = $this->notificationUserParametersRepository->findParametersByUserId($userId);
        } catch (NoResultException $nre) {
            $parameters = $this->createEmptyParameters($userId);
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
        //$typesParams = array();

        $allTypes = array();

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

    public function processUpdate($newParameters, $userId)
    {
        $userParameters = $this->getParametersByUserId($userId);
        $allParameterTypes = $this->allTypesList($userParameters);

        $visibleTypes = array();
        $rssVisibleTypes = array();
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

    private function createEmptyParameters($userId)
    {
        $parameters = new NotificationUserParameters();
        $parameters->setUserId($userId);
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
