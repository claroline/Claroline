<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Controller;

use Claroline\AgendaBundle\Entity\Event;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @EXT\Route("/event")
 */
class EventController extends AbstractCrudController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * EventController constructor.
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param TokenStorageInterface         $tokenStorage
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
    }

    public function getClass()
    {
        return Event::class;
    }

    public function getName()
    {
        return 'event';
    }

    /**
     * @param Request $request
     * @param string  $class
     *
     * @return JsonResponse
     */
    public function listAction(Request $request, $class)
    {
        $query = $request->query->all();
        $hiddenFilters = isset($query['hiddenFilters']) ? $query['hiddenFilters'] : [];
        $query['hiddenFilters'] = array_merge($hiddenFilters, $this->getDefaultHiddenFilters());

        // get start & end date and add them to the hidden filters list
        $query['hiddenFilters']['createdAfter'] = $query['start'];
        $query['hiddenFilters']['endBefore'] = $query['end'];

        $data = $this->finder->search(
            $class,
            $query,
            $this->options['list']
        );

        return new JsonResponse($data['data']);
    }

    /**
     * Marks a list of tasks as done.
     *
     * @EXT\Route("/done", name="apiv2_task_mark_done")
     * @EXT\Method("PUT")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function markDoneAction(Request $request)
    {
        /** @var Event[] $tasks */
        $tasks = $this->decodeIdsString($request, Event::class);
        foreach ($tasks as $task) {
            if ($this->checkPermission($task) && $task->isTask() && !$task->isTaskDone()) {
                $task->setIsTaskDone(true);
                $this->om->persist($task);
            }
        }

        $this->om->flush();

        return new JsonResponse(array_map(function (Event $task) {
            return $this->serializer->serialize($task);
        }, $tasks));
    }

    /**
     * Marks a list of tasks as to do.
     *
     * @EXT\Route("/todo", name="apiv2_task_mark_todo")
     * @EXT\Method("PUT")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function markTodoAction(Request $request)
    {
        /** @var Event[] $tasks */
        $tasks = $this->decodeIdsString($request, Event::class);
        foreach ($tasks as $task) {
            if ($this->checkPermission($task) && $task->isTask() && $task->isTaskDone()) {
                $task->setIsTaskDone(false);
                $this->om->persist($task);
            }
        }

        $this->om->flush();

        return new JsonResponse(array_map(function (Event $task) {
            return $this->serializer->serialize($task);
        }, $tasks));
    }

    /**
     * @EXT\Route("/download", name="apiv2_download_agenda")
     * @EXT\Method("GET")
     *
     * @param Request $request
     *
     * @return StreamedResponse
     */
    public function exportAction(Request $request)
    {
        $id = $request->query->get('workspace');
        $file = $this->container->get('Claroline\AgendaBundle\Manager\AgendaManager')->export($id);

        $response = new StreamedResponse();

        $response->setCallBack(
          function () use ($file) {
              readfile($file);
          }
        );

        $workspace = $this->om->getRepository(Workspace::class)->find($id);
        $name = $workspace ? $workspace->getName() : 'desktop';
        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename='.$name.'.ics');
        $response->headers->set('Content-Type', ' text/calendar');
        $response->headers->set('Connection', 'close');

        return $response;
    }

    /**
     * @EXT\Route("/import", name="apiv2_event_import")
     * @EXT\Method("POST")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function importAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $file = $data['file'];
        $workspace = $data['workspace'];
        $workspace = $workspace['id'] ? $this->om->getObject($workspace, Workspace::class) : null;
        $fileEntity = $this->om->getObject($file, PublicFile::class) ?? new PublicFile();
        $file = $this->serializer->deserialize($file, $fileEntity);
        $fileData = $this->container->get('claroline.utilities.file')->getContents($file);
        $events = $this->container->get('Claroline\AgendaBundle\Manager\AgendaManager')->import($fileData, $workspace);

        return new JsonResponse(array_map(function (Event $event) {
            return $this->serializer->serialize($event);
        }, $events));
    }

    private function checkPermission(Event $event)
    {
        if (false === $event->isEditable()) {
            return false;
        }

        if ($event->getWorkspace()) {
            if (!$this->authorization->isGranted(['agenda', 'edit'], $event->getWorkspace())) {
                return false;
            }

            return true;
        }

        if ($this->tokenStorage->getToken()->getUser() !== $event->getUser()) {
            return false;
        }

        return true;
    }
}
