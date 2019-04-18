<?php

namespace UJM\ExoBundle\Serializer\Item;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Item\Hint;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Entity\Item\ItemObject;
use UJM\ExoBundle\Entity\Item\ItemResource;
use UJM\ExoBundle\Entity\Item\Shared;
use UJM\ExoBundle\Library\Item\ItemDefinitionsCollection;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Repository\ExerciseRepository;
use UJM\ExoBundle\Serializer\Content\ResourceContentSerializer;
use UJM\ExoBundle\Serializer\UserSerializer;

/**
 * Serializer for item data.
 *
 * @DI\Service("ujm_exo.serializer.item")
 * @DI\Tag("claroline.serializer")
 */
class ItemSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var ItemDefinitionsCollection */
    private $itemDefinitions;

    /** @var UserSerializer */
    private $userSerializer;

    /** @var HintSerializer */
    private $hintSerializer;

    /** @var ResourceContentSerializer */
    private $resourceContentSerializer;

    /** @var ItemObjectSerializer */
    private $itemObjectSerializer;

    /** @var ContainerInterface */
    private $container;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * ItemSerializer constructor.
     *
     * @param ObjectManager             $om
     * @param TokenStorageInterface     $tokenStorage
     * @param ItemDefinitionsCollection $itemDefinitions
     * @param UserSerializer            $userSerializer
     * @param HintSerializer            $hintSerializer
     * @param ResourceContentSerializer $resourceContentSerializer
     * @param ItemObjectSerializer      $itemObjectSerializer
     * @param ContainerInterface        $container
     * @param EventDispatcherInterface  $eventDispatcher
     *
     * @DI\InjectParams({
     *     "om"                        = @DI\Inject("claroline.persistence.object_manager"),
     *     "tokenStorage"              = @DI\Inject("security.token_storage"),
     *     "itemDefinitions"           = @DI\Inject("ujm_exo.collection.item_definitions"),
     *     "userSerializer"            = @DI\Inject("ujm_exo.serializer.user"),
     *     "hintSerializer"            = @DI\Inject("ujm_exo.serializer.hint"),
     *     "resourceContentSerializer" = @DI\Inject("ujm_exo.serializer.resource_content"),
     *     "itemObjectSerializer"      = @DI\Inject("ujm_exo.serializer.item_object"),
     *     "container"                 = @DI\Inject("service_container"),
     *     "eventDispatcher"           = @DI\Inject("event_dispatcher")
     * })
     */
    public function __construct(
        ObjectManager $om,
        TokenStorageInterface $tokenStorage,
        ItemDefinitionsCollection $itemDefinitions,
        UserSerializer $userSerializer,
        HintSerializer $hintSerializer,
        ResourceContentSerializer $resourceContentSerializer,
        ItemObjectSerializer $itemObjectSerializer,
        ContainerInterface $container,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;
        $this->itemDefinitions = $itemDefinitions;
        $this->userSerializer = $userSerializer;
        $this->hintSerializer = $hintSerializer;
        $this->resourceContentSerializer = $resourceContentSerializer;
        $this->itemObjectSerializer = $itemObjectSerializer;
        $this->container = $container; // FIXME : this is a cheat to avoid a circular reference with `ujm_exo.manager.item`
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Converts a Item into a JSON-encodable structure.
     *
     * @param Item  $question
     * @param array $options
     *
     * @return array
     */
    public function serialize(Item $question, array $options = [])
    {
        // Serialize specific data for the item type
        $serialized = $this->serializeQuestionType($question, $options);

        if (1 === preg_match('#^application\/x\.[^/]+\+json$#', $question->getMimeType())) {
            $canEdit = $this->tokenStorage->getToken() && $this->tokenStorage->getToken()->getUser() instanceof User ?
                $this->container->get('ujm_exo.manager.item')->canEdit($question, $this->tokenStorage->getToken()->getUser()) :
                false;
            // Adds minimal information
            $serialized = array_merge($serialized, [
                'id' => $question->getUuid(),
                'autoId' => $question->getId(),
                'type' => $question->getMimeType(),
                'content' => $question->getContent(),
                'title' => $question->getTitle(),
                'meta' => $this->serializeMetadata($question, $options),
                'score' => json_decode($question->getScoreRule(), true),
                'rights' => ['edit' => $canEdit],
            ]);

            // Adds full definition of the item
            if (!in_array(Transfer::MINIMAL, $options)) {
                $serialized = array_merge($serialized, [
                    'description' => $question->getDescription(),
                    'hints' => $this->serializeHints($question, $options),
                    'objects' => $this->serializeObjects($question),
                    'resources' => $this->serializeResources($question),
                    'tags' => $this->serializeTags($question),
                ]);

                // Adds item feedback
                if (in_array(Transfer::INCLUDE_SOLUTIONS, $options)) {
                    $serialized['feedback'] = $question->getFeedback();
                }
            }
        } else {
            $serialized = array_merge($serialized, [
                'id' => $question->getUuid(),
                'type' => $question->getMimeType(),
                'title' => $question->getTitle(),
                'meta' => $this->serializeMetadata($question, $options),
            ]);

            // Adds full definition of the item
            if (!in_array(Transfer::MINIMAL, $options)) {
                $serialized = array_merge($serialized, [
                    'description' => $question->getDescription(),
                    'tags' => $this->serializeTags($question),
                ]);
            }
        }

        return $serialized;
    }

    /**
     * Converts raw data into a Item entity.
     *
     * @param array $data
     * @param Item  $item
     * @param array $options
     *
     * @return Item
     */
    public function deserialize($data, Item $item = null, array $options = [])
    {
        if (!in_array(Transfer::NO_FETCH, $options) && empty($item) && !empty($data['id'])) {
            // Loads the Item from DB if already exist
            $item = $this->om->getRepository(Item::class)->findOneBy(['uuid' => $data['id']]);
        }

        $item = $item ?: new Item();
        $this->sipe('id', 'setUuid', $data, $item);

        if (in_array(Transfer::REFRESH_UUID, $options)) {
            $item->refreshUuid();
        }

        // Sets the creator of the Item if not set
        $creator = $item->getCreator();

        if (empty($creator) || !($creator instanceof User)) {
            $token = $this->tokenStorage->getToken();

            if (!empty($token) && $token->getUser() instanceof User) {
                $item->setCreator($token->getUser());
            }
        }

        if (1 === preg_match('#^application\/x\.[^/]+\+json$#', $data['type'])) {
            // question item
            $this->sipe('type', 'setMimeType', $data, $item);
            $this->sipe('content', 'setContent', $data, $item);
            $this->sipe('title', 'setTitle', $data, $item);
            $this->sipe('description', 'setDescription', $data, $item);
            $this->sipe('feedback', 'setFeedback', $data, $item);

            if (isset($data['hints'])) {
                $this->deserializeHints($item, $data['hints'], $options);
            }
            if (isset($data['objects'])) {
                $this->deserializeObjects($item, $data['objects'], $options);
            }
            if (isset($data['resources'])) {
                $this->deserializeResources($item, $data['resources'], $options);
            }
            if (isset($data['meta'])) {
                $this->deserializeMetadata($item, $data['meta']);
            }
            if (isset($data['score'])) {
                $score = $this->sanitizeScore($data['score']);
                $item->setScoreRule(json_encode($score));
            }
        } else {
            // content item
            $this->sipe('type', 'setMimeType', $data, $item);
            $this->sipe('title', 'setTitle', $data, $item);
            $this->sipe('description', 'setDescription', $data, $item);
        }

        $this->deserializeQuestionType($item, $data, $options);

        if (isset($data['tags'])) {
            $this->deserializeTags($item, $data['tags'], $options);
        }

        return $item;
    }

    /**
     * Serializes Item data specific to its type.
     * Forwards the serialization to the correct handler.
     *
     * @param Item  $question
     * @param array $options
     *
     * @return array
     */
    private function serializeQuestionType(Item $question, array $options = [])
    {
        $type = $this->itemDefinitions->getConvertedType($question->getMimeType());
        $definition = $this->itemDefinitions->get($type);

        return $definition->serializeQuestion($question->getInteraction(), $options);
    }

    /**
     * Deserializes Item data specific to its type.
     * Forwards the serialization to the correct handler.
     *
     * @param Item  $question
     * @param array $data
     * @param array $options
     */
    private function deserializeQuestionType(Item $question, array $data, array $options = [])
    {
        $type = $this->itemDefinitions->getConvertedType($question->getMimeType());
        $definition = $this->itemDefinitions->get($type);

        // Deserialize item type data
        $type = $definition->deserializeQuestion($data, $question->getInteraction(), $options);
        $type->setQuestion($question);
        if (in_array(Transfer::REFRESH_UUID, $options)) {
            $definition->refreshIdentifiers($question->getInteraction());
        }
    }

    /**
     * Serializes Item metadata.
     *
     * @param Item  $question
     * @param array $options
     *
     * @return array
     */
    private function serializeMetadata(Item $question, array $options = [])
    {
        $metadata = ['protectQuestion' => $question->getProtectUpdate()];

        $creator = $question->getCreator();

        if (!empty($creator)) {
            $metadata['creator'] = $this->userSerializer->serialize($creator, $options);
            // TODO : remove me. for retro compatibility with old schema
            $metadata['authors'] = [$this->userSerializer->serialize($creator, $options)];
        }

        if ($question->getDateCreate()) {
            $metadata['created'] = DateNormalizer::normalize($question->getDateCreate());
        }

        if ($question->getDateModify()) {
            $metadata['updated'] = DateNormalizer::normalize($question->getDateModify());
        }

        if (in_array(Transfer::INCLUDE_ADMIN_META, $options)) {
            /** @var ExerciseRepository $exerciseRepo */
            $exerciseRepo = $this->om->getRepository(Exercise::class);

            // Gets exercises that use this item
            $exercises = $exerciseRepo->findByQuestion($question);
            $metadata['usedBy'] = array_map(function (Exercise $exercise) {
                return $exercise->getUuid();
            }, $exercises);

            // Gets users who have access to this item
            $users = $this->om->getRepository(Shared::class)->findBy(['question' => $question]);
            $metadata['sharedWith'] = array_map(function (Shared $sharedQuestion) use ($options) {
                $shared = [
                    'adminRights' => $sharedQuestion->hasAdminRights(),
                    'user' => $this->userSerializer->serialize($sharedQuestion->getUser(), $options),
                ];

                return $shared;
            }, $users);
        }

        return $metadata;
    }

    /**
     * Deserializes Item metadata.
     *
     * @param Item  $question
     * @param array $metadata
     */
    public function deserializeMetadata(Item $question, array $metadata)
    {
        $this->sipe('protectQuestion', 'setProtectUpdate', $metadata, $question);
    }

    /**
     * Serializes Item hints.
     * Forwards the hint serialization to HintSerializer.
     *
     * @param Item  $question
     * @param array $options
     *
     * @return array
     */
    private function serializeHints(Item $question, array $options = [])
    {
        return array_map(function (Hint $hint) use ($options) {
            return $this->hintSerializer->serialize($hint, $options);
        }, $question->getHints()->toArray());
    }

    /**
     * Deserializes Item hints.
     * Forwards the hint deserialization to HintSerializer.
     *
     * @param Item  $question
     * @param array $hints
     * @param array $options
     */
    private function deserializeHints(Item $question, array $hints = [], array $options = [])
    {
        $hintEntities = $question->getHints()->toArray();

        foreach ($hints as $hintData) {
            $existingHint = null;

            // Searches for an existing hint entity.
            foreach ($hintEntities as $entityIndex => $entityHint) {
                /** @var Hint $entityHint */
                if ($entityHint->getUuid() === $hintData['id']) {
                    $existingHint = $entityHint;
                    unset($hintEntities[$entityIndex]);
                    break;
                }
            }

            $entity = $this->hintSerializer->deserialize($hintData, $existingHint, $options);

            if (empty($existingHint)) {
                // Creation of a new hint (we need to link it to the question)
                $question->addHint($entity);
            }
        }

        // Remaining hints are no longer in the Exercise
        if (0 < count($hintEntities)) {
            foreach ($hintEntities as $hintToRemove) {
                $question->removeHint($hintToRemove);
            }
        }
    }

    /**
     * Serializes Item objects.
     * Forwards the object serialization to ItemObjectSerializer.
     *
     * @param Item  $question
     * @param array $options
     *
     * @return array
     */
    private function serializeObjects(Item $question, array $options = [])
    {
        return array_values(array_map(function (ItemObject $object) use ($options) {
            return $this->itemObjectSerializer->serialize($object, $options);
        }, $question->getObjects()->toArray()));
    }

    /**
     * Deserializes Item objects.
     *
     * @param Item  $question
     * @param array $objects
     * @param array $options
     */
    private function deserializeObjects(Item $question, array $objects = [], array $options = [])
    {
        $objectEntities = $question->getObjects()->toArray();
        $question->emptyObjects();

        foreach ($objects as $index => $objectData) {
            $existingObject = null;

            // Searches for an existing object entity.
            foreach ($objectEntities as $entityIndex => $entityObject) {
                /** @var ItemObject $entityObject */
                if ($entityObject->getUuid() === $objectData['id']) {
                    $existingObject = $entityObject;
                    unset($objectEntities[$entityIndex]);
                    break;
                }
            }
            $itemObject = $this->itemObjectSerializer->deserialize($objectData, $existingObject, $options);
            $itemObject->setOrder($index);
            $question->addObject($itemObject);
        }

        // Remaining objects are no longer in the Item
        if (0 < count($objectEntities)) {
            foreach ($objectEntities as $objectToRemove) {
                $this->om->remove($objectToRemove);
            }
        }
    }

    /**
     * Serializes Item resources.
     * Forwards the resource serialization to ResourceContentSerializer.
     *
     * @param Item  $question
     * @param array $options
     *
     * @return array
     */
    private function serializeResources(Item $question, array $options = [])
    {
        return array_map(function (ItemResource $resource) use ($options) {
            return $this->resourceContentSerializer->serialize($resource->getResourceNode(), $options);
        }, $question->getResources()->toArray());
    }

    /**
     * Deserializes Item resources.
     *
     * @param Item  $question
     * @param array $resources
     * @param array $options
     */
    private function deserializeResources(Item $question, array $resources = [], array $options = [])
    {
        $resourceEntities = $question->getResources()->toArray();

        foreach ($resources as $resourceData) {
            $existingResource = null;

            // Searches for an existing resource entity.
            foreach ($resourceEntities as $entityIndex => $entityResource) {
                /** @var ItemResource $entityResource */
                if ((string) $entityResource->getId() === $resourceData['id']) {
                    $existingResource = $entityResource;
                    unset($resourceEntities[$entityIndex]);
                    break;
                }
            }

            // Link resource to item
            if (empty($existingResource)) {
                $obj = $this->resourceContentSerializer->deserialize($resourceData, $existingResource, $options);
                if ($obj) {
                    if ($obj instanceof ResourceNode) {
                        $itemResource = new ItemResource();
                        $itemResource->setResourceNode($obj);
                        $itemResource->setQuestion($question);
                        $obj = $itemResource;
                    }
                    $question->addResource($obj);
                }
            }
        }

        // Remaining resources are no longer in the Item
        if (0 < count($resourceEntities)) {
            foreach ($resourceEntities as $resourceToRemove) {
                $question->removeResource($resourceToRemove);
            }
        }
    }

    /**
     * The client may send dirty data, we need to clean them before storing it in DB.
     *
     * @param $score
     *
     * @return array
     */
    private function sanitizeScore($score)
    {
        $sanitized = ['type' => $score['type']];

        switch ($score['type']) {
            case 'fixed':
                $sanitized['success'] = $score['success'];
                $sanitized['failure'] = $score['failure'];
                break;

            case 'manual':
                $sanitized['max'] = $score['max'];
                break;

            case 'rules':
                $sanitized['noWrongChoice'] = isset($score['noWrongChoice']) ? $score['noWrongChoice'] : false;
                $sanitized['rules'] = $score['rules'];
                break;
        }

        return $sanitized;
    }

    /**
     * Serializes Item tags.
     * Forwards the tag serialization to ItemTagSerializer.
     *
     * @param Item $question
     *
     * @return array
     */
    private function serializeTags(Item $question)
    {
        $event = new GenericDataEvent([
            'class' => Item::class,
            'ids' => [$question->getUuid()],
        ]);
        $this->eventDispatcher->dispatch('claroline_retrieve_used_tags_by_class_and_ids', $event);

        return $event->getResponse();
    }

    /**
     * Deserializes Item tags.
     *
     * @param Item  $question
     * @param array $tags
     * @param array $options
     */
    private function deserializeTags(Item $question, array $tags = [], array $options = [])
    {
        if (in_array(Transfer::PERSIST_TAG, $options)) {
            $user = null;

            if ($this->tokenStorage->getToken() && $this->tokenStorage->getToken()->getUser() instanceof User) {
                $user = $this->tokenStorage->getToken()->getUser();
            }

            $event = new GenericDataEvent([
                'user' => $user,
                'tags' => $tags,
                'data' => [
                    [
                        'class' => Item::class,
                        'id' => $question->getUuid(),
                        'name' => $question->getTitle(),
                    ],
                ],
                'replace' => true,
            ]);

            $this->eventDispatcher->dispatch('claroline_tag_multiple_data', $event);
        }
    }
}
