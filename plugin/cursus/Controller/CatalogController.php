<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Controller;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Entity\CourseSessionRegistrationQueue;
use Claroline\CursusBundle\Entity\CourseSessionUser;
use Claroline\CursusBundle\Entity\SessionEventUser;
use Claroline\CursusBundle\Manager\CursusManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;

class CatalogController
{
    /** @var CursusManager */
    protected $cursusManager;

    /** @var FinderProvider */
    protected $finder;

    /** @var SerializerProvider */
    protected $serializer;

    private $sessionUserRepo;
    private $sessionQueueRepo;

    /**
     * CatalogController constructor.
     *
     * @param CursusManager      $cursusManager
     * @param FinderProvider     $finder
     * @param ObjectManager      $om
     * @param SerializerProvider $serializer
     */
    public function __construct(
        CursusManager $cursusManager,
        FinderProvider $finder,
        ObjectManager $om,
        SerializerProvider $serializer
    ) {
        $this->cursusManager = $cursusManager;
        $this->finder = $finder;
        $this->serializer = $serializer;

        $this->sessionUserRepo = $om->getRepository(CourseSessionUser::class);
        $this->sessionQueueRepo = $om->getRepository(CourseSessionRegistrationQueue::class);
    }

    /**
     * @EXT\Route(
     *     "/catalog/cursus/session/{session}",
     *     name="claro_cursus_catalog_session"
     * )
     * @EXT\ParamConverter(
     *     "session",
     *     class="ClarolineCursusBundle:CourseSession",
     *     options={"mapping": {"session": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     *
     * @param CourseSession $session
     * @param User          $user
     *
     * @return JsonResponse
     */
    public function cursusCatalogSessionAction(CourseSession $session, User $user = null)
    {
        $sessionUser = !is_null($user) ?
            $this->sessionUserRepo->findOneBy([
                'session' => $session,
                'user' => $user,
                'userType' => CourseSessionUser::TYPE_LEARNER,
            ]) :
            null;
        $sessionQueue = !is_null($user) ?
            $this->sessionQueueRepo->findOneBy([
                'session' => $session,
                'user' => $user,
            ]) :
            null;
        $isFull = !$this->cursusManager->checkSessionCapacity($session);

        $eventsRegistration = [];
        $eventUsers = !is_null($user) ?
            $this->finder->fetch(SessionEventUser::class, ['session' => $session->getUuid(), 'user' => $user->getUuid()]) :
            [];

        foreach ($eventUsers as $eventUser) {
            $event = $eventUser->getSessionEvent();
            $set = $event->getEventSet();
            $eventsRegistration[$event->getUuid()] = true;

            if ($set) {
                $setName = $set->getName();

                if (!isset($eventsRegistration[$setName])) {
                    $eventsRegistration[$setName] = $set->getLimit();
                }
                --$eventsRegistration[$setName];
            }
        }

        return new JsonResponse([
            'session' => $this->serializer->serialize($session),
            'sessionUser' => $sessionUser ? $this->serializer->serialize($sessionUser) : null,
            'sessionQueue' => $sessionQueue ? $this->serializer->serialize($sessionQueue) : null,
            'isFull' => $isFull,
            'eventsRegistration' => $eventsRegistration,
        ]);
    }
}
