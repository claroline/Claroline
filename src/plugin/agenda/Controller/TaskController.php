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

use Claroline\AgendaBundle\Entity\Task;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/task', name: 'apiv2_task_')]
class TaskController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization
    ) {
        $this->authorization = $authorization;
    }

    public static function getClass(): string
    {
        return Task::class;
    }

    public static function getName(): string
    {
        return 'task';
    }

    /**
     * Marks a list of tasks as done.
     */
    #[Route(path: '/done', name: 'mark_done', methods: ['PUT'])]
    public function markDoneAction(Request $request): JsonResponse
    {
        /** @var Task[] $tasks */
        $tasks = $this->decodeIdsString($request, Task::class);
        foreach ($tasks as $task) {
            if (!$task->isDone() && $this->checkPermission('EDIT', $task)) {
                $task->setDone(true);
                $this->om->persist($task);
            }
        }

        $this->om->flush();

        return new JsonResponse(array_map(function (Task $task) {
            return $this->serializer->serialize($task);
        }, $tasks));
    }

    /**
     * Marks a list of tasks as to do.
     */
    #[Route(path: '/todo', name: 'mark_todo', methods: ['PUT'])]
    public function markTodoAction(Request $request): JsonResponse
    {
        /** @var Task[] $tasks */
        $tasks = $this->decodeIdsString($request, Task::class);
        foreach ($tasks as $task) {
            if ($task->isDone() && $this->checkPermission('EDIT', $task)) {
                $task->setDone(false);
                $this->om->persist($task);
            }
        }

        $this->om->flush();

        return new JsonResponse(array_map(function (Task $task) {
            return $this->serializer->serialize($task);
        }, $tasks));
    }
}
