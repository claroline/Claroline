<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\LinkBundle\Listener\Resource\Types;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\EvaluationBundle\Component\Resource\EvaluatedResourceInterface;
use Claroline\LinkBundle\Entity\Resource\Shortcut;
use Doctrine\Persistence\ObjectRepository;

/**
 * Integrates the "Shortcut" resource.
 */
class ShortcutListener extends ResourceComponent implements EvaluatedResourceInterface
{
    private ObjectRepository $repository;

    public function __construct(
        ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly Crud $crud
    ) {
        $this->repository = $om->getRepository(Shortcut::class);
    }

    public static function getSubscribedEvents(): array
    {
        return array_merge(parent::getSubscribedEvents(), [
            Crud::getEventName('delete', 'post', ResourceNode::class) => 'cleanShortcuts',
        ]);
    }

    public static function getName(): string
    {
        return 'shortcut';
    }

    /** @var Shortcut $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        return [
            'resource' => $this->serializer->serialize($resource),
        ];
    }

    public function update(AbstractResource $resource, array $data): ?array
    {
        return [
            'resource' => $this->serializer->serialize($resource),
        ];
    }

    /**
     * Removes all linked shortcuts when a resource is deleted.
     */
    public function cleanShortcuts(DeleteEvent $event): void
    {
        $resourceNode = $event->getObject();

        // retrieve the list of shortcuts associated to the ResourceNode
        /** @var Shortcut[] $shortcuts */
        $shortcuts = $this->repository->findBy(['target' => $resourceNode]);
        if (!empty($shortcuts)) {
            $this->crud->deleteBulk($shortcuts, $event->getOptions());
        }
    }
}
