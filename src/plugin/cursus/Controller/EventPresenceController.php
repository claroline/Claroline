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

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Manager\PdfManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Security\ToolPermissions;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\CursusBundle\Component\Tool\TrainingEventsTool;
use Claroline\CursusBundle\Entity\Event;
use Claroline\CursusBundle\Entity\EventPresence;
use Claroline\CursusBundle\Manager\EventManager;
use Claroline\CursusBundle\Manager\EventPresenceManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/cursus_event_presence")
 */
class EventPresenceController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly FinderProvider $finder,
        private readonly SerializerProvider $serializer,
        private readonly EventPresenceManager $manager,
        private readonly EventManager $eventManager,
        private readonly PdfManager $pdfManager,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly TranslatorInterface $translator,
        private readonly Crud $crud
    ) {
        $this->authorization = $authorization;
    }

    /**
     * Updates the status of a EventPresence for current user.
     *
     * @Route("/sign", name="apiv2_cursus_event_presence_sign", methods={"PUT"})
     */
    public function signStatusAction(Request $request): JsonResponse
    {
        $data = $this->decodeRequest($request);
        if (empty($data)) {
            throw new InvalidDataException('Invalid data');
        }

        $event = $data['event'];
        $signature = trim($data['signature']);

        $eventObject = $this->om->getRepository(Event::class)->findOneBy([
            'uuid' => $event['id'],
        ]);

        $presence = $this->om->getRepository(EventPresence::class)->findOneBy([
            'event' => $eventObject,
            'user' => $this->tokenStorage->getToken()->getUser(),
        ]);

        if (!$presence) {
            return new JsonResponse(null, 404);
        }

        if (EventPresence::PRESENT === $presence->getStatus()) {
            return new JsonResponse(['success' => false]);
        }

        $presenceData = $this->serializer->serialize($presence);
        $presenceData['status'] = EventPresence::PRESENT;
        $presenceData['signature'] = $signature;

        $this->crud->update($presence, $presenceData);

        return new JsonResponse(['success' => true]);
    }

    /**
     * Confirm the status of a EventPresence for current user.
     *
     * @Route("/confirm", name="apiv2_cursus_event_presence_confirm", methods={"PUT"})
     */
    public function confirmStatusAction(Request $request): JsonResponse
    {
        $data = $this->decodeRequest($request);
        if (empty($data)) {
            throw new InvalidDataException('Invalid data');
        }

        $presences = $this->om->getRepository(EventPresence::class)->findBy(['uuid' => $data]);
        $this->om->startFlushSuite();
        foreach ($presences as $presence) {
            $this->checkPermission('ADMINISTRATE', $presence, [], true);

            $this->manager->setValidationDate([$presence], new \DateTime());
        }
        $this->om->endFlushSuite();

        return new JsonResponse();
    }

    /**
     * @Route("/check/{code}", name="apiv2_cursus_event_presence_check", methods={"GET"})
     */
    public function getEventPresenceByCodeAction(string $code): JsonResponse
    {
        $event = $this->om->getRepository(Event::class)->findOneBy(['code' => $code]);
        if (!$event) {
            return new JsonResponse(null, 404);
        }

        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user || 'anon.' === $user) {
            return new JsonResponse(null, 401);
        }

        $presence = $this->om->getRepository(EventPresence::class)->findOneBy([
            'event' => $event,
            'user' => $user,
        ]);

        return new JsonResponse($this->serializer->serialize($presence));
    }

    /**
     * @Route("/{id}", name="apiv2_cursus_event_presence_list", methods={"GET"})
     *
     * @EXT\ParamConverter("event", class="Claroline\CursusBundle\Entity\Event", options={"mapping": {"id": "uuid"}})
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
     * @Route("/workspace/{id}", name="apiv2_cursus_workspace_presence_list", methods={"GET"})
     *
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"id": "uuid"}})
     */
    public function listByWorkspaceAction(Workspace $workspace, Request $request): JsonResponse
    {
        $isManager = $this->checkPermission(ToolPermissions::getPermission(TrainingEventsTool::getName(), 'EDIT'), $workspace, [])
            || $this->checkPermission(ToolPermissions::getPermission(TrainingEventsTool::getName(), 'REGISTER'), $workspace, []);

        $params = $request->query->all();
        $params['hiddenFilters'] = [
            'workspace' => $workspace->getUuid(),
        ];

        if (!$isManager) {
            $params['hiddenFilters']['user'] = $this->tokenStorage->getToken()->getUser()->getUuid();
        }

        return new JsonResponse(
            $this->finder->search(EventPresence::class, $params)
        );
    }

    /**
     * Updates the status of an EventPresence list.
     *
     * @Route("/status/{status}", name="apiv2_cursus_event_presence_update", methods={"PUT"})
     */
    public function updateStatusAction(string $status, Request $request): JsonResponse
    {
        $data = $this->decodeRequest($request);
        if (empty($data)) {
            return new JsonResponse(null, 404);
        }

        $presences = $this->om->getRepository(EventPresence::class)->findBy(['uuid' => $data]);
        $this->om->startFlushSuite();
        foreach ($presences as $presence) {
            $this->checkPermission('EDIT', $presence, [], true);

            $this->manager->setStatus([$presence], $status);
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (EventPresence $presence) {
            return $this->serializer->serialize($presence);
        }, $presences));
    }

    /**
     * @Route("/{id}/download/{filled}", name="apiv2_cursus_event_presence_download", methods={"GET"})
     *
     * @EXT\ParamConverter("event", class="Claroline\CursusBundle\Entity\Event", options={"mapping": {"id": "uuid"}})
     */
    public function downloadPdfAction(Event $event, Request $request, int $filled): StreamedResponse
    {
        $this->checkPermission('EDIT', $event, [], true);

        return new StreamedResponse(function () use ($event, $request, $filled): void {
            echo $this->pdfManager->fromHtml(
                $this->manager->download($event, $this->eventManager->getRegisteredUsers($event), $request->getLocale(), (bool) $filled)
            );
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($event->getName()).'-presences.pdf',
        ]);
    }

    /**
     * @Route("/{id}/pdf", name="apiv2_cursus_user_presence_download", methods={"GET"})
     *
     * @EXT\ParamConverter("eventPresence", class="Claroline\CursusBundle\Entity\EventPresence", options={"mapping": {"id": "uuid"}})
     */
    public function downloadUserPdfAction(EventPresence $eventPresence, Request $request): StreamedResponse
    {
        $this->checkPermission('OPEN', $eventPresence, [], true);

        return new StreamedResponse(function () use ($eventPresence, $request): void {
            echo $this->pdfManager->fromHtml(
                $this->manager->downloadUser($eventPresence, $request->getLocale())
            );
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($eventPresence->getEvent()->getName()).'-presence.pdf',
        ]);
    }

    /**
     * @Route("/{id}/evidences", name="apiv2_cursus_presence_evidences_upload", methods={"POST"})
     *
     * @EXT\ParamConverter("eventPresence", class="Claroline\CursusBundle\Entity\EventPresence", options={"mapping": {"id": "uuid"}})
     */
    public function uploadEvidences(EventPresence $eventPresence, Request $request): JsonResponse
    {
        $this->checkPermission('EDIT', $eventPresence, [], true);

        $files = $request->files->all();

        $evidences = [];
        foreach ($files as $index => $file) {
            $evidenceFile = $this->manager->uploadEvidence($file, $eventPresence);
            $evidences[] = [
                'type' => $evidenceFile->getMimeType(),
                'mimeType' => $evidenceFile->getMimeType(),
                'name' => $evidenceFile->getFilename(),
                'size' => $evidenceFile->getSize(),
                'url' => $evidenceFile->getRealPath(),
                'num' => $index + 1,
            ];
        }

        $eventPresence->setEvidences($evidences);

        $this->om->persist($eventPresence);
        $this->om->flush();

        return new JsonResponse($this->serializer->serialize($eventPresence));
    }

    /**
     * @Route("/{id}/evidences", name="apiv2_cursus_presence_evidence_download", methods={"GET"})
     *
     * @EXT\ParamConverter("eventPresence", class="Claroline\CursusBundle\Entity\EventPresence", options={"mapping": {"id": "uuid"}})
     */
    public function downloadEvidenceAction(EventPresence $eventPresence, Request $request): StreamedResponse
    {
        $this->checkPermission('OPEN', $eventPresence, [], true);

        $file = $request->get('file');
        $content = file_get_contents($file['url']);
        $downloadedName = $this->translator->trans('evidence', [], 'presence').'-'.$file['num'].'-'.$eventPresence->getUser()->getUsername().'-'.$eventPresence->getEvent()->getCode();
        $extension = pathinfo($file['url'], \PATHINFO_EXTENSION);

        return new StreamedResponse(function () use ($content): void {
            echo $content;
        }, 200, [
            'Content-Type' => $file['type'],
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($downloadedName).'.'.$extension,
        ]);
    }
}
