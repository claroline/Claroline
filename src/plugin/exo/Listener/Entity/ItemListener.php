<?php

namespace UJM\ExoBundle\Listener\Entity;

use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Library\Item\ItemDefinitionsCollection;

/**
 * Manages Life cycle of the Item.
 */
class ItemListener
{
    public function __construct(
        private readonly ItemDefinitionsCollection $itemDefinitions
    ) {
    }

    /**
     * Loads the entity that holds the item type data when an Item is loaded.
     */
    public function postLoad(Item $item, PostLoadEventArgs $event): void
    {
        $type = $this->itemDefinitions->getConvertedType($item->getMimeType());
        $definition = $this->itemDefinitions->get($type);
        $repository = $event
            ->getObjectManager()
            ->getRepository($definition->getEntityClass());

        /** @var AbstractItem $typeEntity */
        $typeEntity = $repository->findOneBy([
            'question' => $item,
        ]);

        if (!empty($typeEntity)) {
            $item->setInteraction($typeEntity);
        }
    }

    /**
     * Persists the entity that holds the item type data when an Item is persisted.
     */
    public function prePersist(Item $item, PrePersistEventArgs $event): void
    {
        $interaction = $item->getInteraction();
        if (null !== $interaction) {
            $event->getObjectManager()->persist($interaction);
        }
    }

    /**
     * Deletes the entity that holds the item type data when an Item is deleted.
     */
    public function preRemove(Item $item, PreRemoveEventArgs $event): void
    {
        $interaction = $item->getInteraction();
        if (null !== $interaction) {
            $event->getObjectManager()->remove($interaction);
        }
    }
}
