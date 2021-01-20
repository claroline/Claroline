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
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/event")
 */
class EventController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var RequestStack */
    private $requestStack;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
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
     * Marks a list of tasks as done.
     *
     * @Route("/done", name="apiv2_task_mark_done", methods={"PUT"})
     */
    public function markDoneAction(Request $request): JsonResponse
    {
        /** @var Event[] $tasks */
        $tasks = $this->decodeIdsString($request, Event::class);
        foreach ($tasks as $task) {
            if ($task->isTask() && !$task->isTaskDone() && $this->checkPermission('EDIT', $task)) {
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
     * @Route("/todo", name="apiv2_task_mark_todo", methods={"PUT"})
     */
    public function markTodoAction(Request $request): JsonResponse
    {
        /** @var Event[] $tasks */
        $tasks = $this->decodeIdsString($request, Event::class);
        foreach ($tasks as $task) {
            if ($task->isTask() && $task->isTaskDone() && $this->checkPermission('EDIT', $task)) {
                $task->setIsTaskDone(false);
                $this->om->persist($task);
            }
        }

        $this->om->flush();

        return new JsonResponse(array_map(function (Event $task) {
            return $this->serializer->serialize($task);
        }, $tasks));
    }

    protected function getDefaultHiddenFilters(): array
    {
        $hiddenFilters = [];

        $query = $this->requestStack->getCurrentRequest()->query->all();

        // get start & end date and add them to the hidden filters list
        $hiddenFilters['inRange'] = [$query['start'] ?? null, $query['end'] ?? null];

        if (!isset($query['filters']['workspaces'])) {
            /** @var User $user */
            $user = $this->tokenStorage->getToken()->getUser();
            if ('anon.' !== $user) {
                $hiddenFilters['user'] = $user->getUuid();
            } else {
                $hiddenFilters['anonymous'] = true;
            }
        }

        return $hiddenFilters;
    }
}
