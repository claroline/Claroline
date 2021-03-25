<?php

namespace UJM\ExoBundle\Listener\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UJM\ExoBundle\Entity\Item\Item;
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

    /**
     * ItemListener constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->itemDefinitions = $container->get('ujm_exo.collection.item_definitions');
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

        /** @var \UJM\ExoBundle\Entity\ItemType\AbstractItem $typeEntity */
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
