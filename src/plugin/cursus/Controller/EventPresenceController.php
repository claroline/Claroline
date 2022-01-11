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
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Manager\PdfManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CursusBundle\Entity\Event;
use Claroline\CursusBundle\Entity\EventPresence;
use Claroline\CursusBundle\Manager\EventManager;
use Claroline\CursusBundle\Manager\EventPresenceManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/cursus_event_presence")
 */
class EventPresenceController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var FinderProvider */
    private $finder;
    /** @var SerializerProvider */
    private $serializer;
    /** @var EventPresenceManager */
    private $manager;
    /** @var EventManager */
    private $eventManager;
    /** @var PdfManager */
    private $pdfManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        FinderProvider $finder,
        SerializerProvider $serializer,
        EventPresenceManager $manager,
        EventManager $eventManager,
        PdfManager $pdfManager
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->finder = $finder;
        $this->serializer = $serializer;
        $this->manager = $manager;
        $this->eventManager = $eventManager;
        $this->pdfManager = $pdfManager;
    }

    /**
     * @Route("/{id}", name="apiv2_cursus_event_presence_list", methods={"GET"})
     * @EXT\ParamConverter("event", class="ClarolineCursusBundle:Event", options={"mapping": {"id": "uuid"}})
     */
    public function listAction(Event $event, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $event, [], true);

        // not optimal, as it will do it for each new search
        $this->manager->generate($event, $this->eventManager->getRegisteredUsers($event));

        $params = $request->query->all();
        $params['hiddenFilters'] = [
            'event' => $event->getUuid(),
        ];

        return new JsonResponse(
            $this->finder->search(EventPresence::class, $params)
        );
    }

    /**
     * Updates the status of an EventPresence list.
     *
     * @Route("/{id}/{status}", name="apiv2_cursus_event_presence_update", methods={"PUT"})
     * @EXT\ParamConverter("event", class="ClarolineCursusBundle:Event", options={"mapping": {"id": "uuid"}})
     */
    public function updateStatusAction(Event $event, string $status, Request $request): JsonResponse
    {
        $this->checkPermission('EDIT', $event, [], true);

        $presences = $this->manager->setStatus($this->decodeIdsString($request, EventPresence::class), $status);

        return new JsonResponse(array_map(function (EventPresence $presence) {
            return $this->serializer->serialize($presence);
        }, $presences));
    }

    /**
     * @Route("/{id}/download/{filled}", name="apiv2_cursus_event_presence_download", methods={"GET"})
     * @EXT\ParamConverter("event", class="ClarolineCursusBundle:Event", options={"mapping": {"id": "uuid"}})
     */
    public function downloadPdfAction(Event $event, Request $request, int $filled): StreamedResponse
    {
        $this->checkPermission('EDIT', $event, [], true);

        return new StreamedResponse(function () use ($event, $request, $filled) {
            echo $this->pdfManager->fromHtml(
                $this->manager->download($event, $this->eventManager->getRegisteredUsers($event), $request->getLocale(), (bool) $filled)
            );
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($event->getName()).'-presences.pdf',
        ]);
    }

    /**
     * @Route("/{id}/user/{userId}/download", name="apiv2_cursus_user_presence_download", methods={"GET"})
     * @EXT\ParamConverter("event", class="ClarolineCursusBundle:Event", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", class="ClarolineCoreBundle:User", options={"mapping": {"userId": "uuid"}})
     */
    public function downloadUserPdfAction(Event $event, User $user, Request $request): StreamedResponse
    {
        $this->checkPermission('EDIT', $event, [], true);

        return new StreamedResponse(function () use ($event, $request, $user) {
            echo $this->pdfManager->fromHtml(
                $this->manager->downloadUser($event, $request->getLocale(), $user)
            );
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($event->getName()).'-presence.pdf',
        ]);
    }
}
