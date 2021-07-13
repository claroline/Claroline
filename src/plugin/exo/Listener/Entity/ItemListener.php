<?php

namespace UJM\ExoBundle\Listener\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Library\Item\ItemDefinitionsCollection;

/**
 * Manages Life cycle of the Item.
 */
class ItemListener
{
    /**
     * @var ItemDefinitionsCollection
     */
    private $itemDefinitions;

    public function __construct(ItemDefinitionsCollection $itemDefinitions)
    {
        $this->itemDefinitions = $itemDefinitions;
    }

    /**
     * Loads the entity that holds the item type data when an Item is loaded.
     */
    public function postLoad(Item $item, LifecycleEventArgs $event)
    {
        $type = $this->itemDefinitions->getConvertedType($item->getMimeType());
        $definition = $this->itemDefinitions->get($type);
        $repository = $event
            ->getEntityManager()
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
    public function prePersist(Item $item, LifecycleEventArgs $event)
    {
        $interaction = $item->getInteraction();
        if (null !== $interaction) {
            $event->getEntityManager()->persist($interaction);
        }
    }

    /**
     * Deletes the entity that holds the item type data when an Item is deleted.
     */
    public function preRemove(Item $item, LifecycleEventArgs $event)
    {
        $interaction = $item->getInteraction();
        if (null !== $interaction) {
            $event->getEntityManager()->remove($interaction);
        }
    }
}
