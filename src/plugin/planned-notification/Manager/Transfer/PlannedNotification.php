<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PlannedNotificationBundle\Manager\Transfer;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Workspace\Transfer\Tools\ToolImporterInterface;
use Claroline\PlannedNotificationBundle\Entity\Message;
use Claroline\PlannedNotificationBundle\Entity\PlannedNotification as Planned;

class PlannedNotification implements ToolImporterInterface
{
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;

    public function __construct(
        ObjectManager $om,
        Crud $crud
    ) {
        $this->om = $om;
        $this->crud = $crud;
    }

    public function serialize(Workspace $workspace, array $options): array
    {
        return [
            'planned' => $this->crud->list(Planned::class, ['filters' => ['workspace' => $workspace->getUuid()]])['data'],
            'messages' => $this->crud->list(Message::class, ['filters' => ['workspace' => $workspace->getUuid()]])['data'],
        ];
    }

    public function deserialize(array $data, Workspace $workspace, array $options, array $newEntities, FileBag $bag): array
    {
        $messages = [];

        foreach ($data['messages'] as $message) {
            $this->om->startFlushSuite();
            $new = $this->crud->create(Message::class, $message, $options);
            $new->setWorkspace($workspace);
            $new->emptyNotifications();

            $this->om->persist($new);
            $this->om->endFlushSuite();

            $messages[$message['id']] = $new;
        }

        return $messages;
    }

    public function prepareImport(array $orderedToolData, array $data): array
    {
        return $data;
    }
}
