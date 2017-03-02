<?php

namespace UJM\ExoBundle\Listener\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Library\Item\ItemDefinitionsCollection;

/**
 * Manages Life cycle of the Item.
 *
 * @DI\Service("ujm_exo.listener.entity_item")
 * @DI\Tag("doctrine.entity_listener")
 */
class ItemListener
{
    /**
     * @var ItemDefinitionsCollection
     */
    private $itemDefinitions;

    /**
     * ItemListener constructor.
     *
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->itemDefinitions = $container->get('ujm_exo.collection.item_definitions');
    }

    /**
     * Loads the entity that holds the item type data when an Item is loaded.
     *
     * @param Item               $item
     * @param LifecycleEventArgs $event
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
     *
     * @param Item               $item
     * @param LifecycleEventArgs $event
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
     *
     * @param Item               $item
     * @param LifecycleEventArgs $event
     */
    public function preRemove(Item $item, LifecycleEventArgs $event)
    {
        $interaction = $item->getInteraction();
        if (null !== $interaction) {
            $event->getEntityManager()->remove($interaction);
        }
    }
}
