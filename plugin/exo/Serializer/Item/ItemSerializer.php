<?php

namespace UJM\ExoBundle\Serializer\Item;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Persistence\ObjectManager;
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
use UJM\ExoBundle\Library\Serializer\AbstractSerializer;
use UJM\ExoBundle\Repository\ExerciseRepository;
use UJM\ExoBundle\Serializer\Content\ResourceContentSerializer;
use UJM\ExoBundle\Serializer\UserSerializer;

/**
 * Serializer for item data.
 *
 * @DI\Service("ujm_exo.serializer.item")
 */
class ItemSerializer extends AbstractSerializer
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var ItemDefinitionsCollection
     */
    private $itemDefinitions;

    /**
     * @var UserSerializer
     */
    private $userSerializer;

    /**
     * @var CategorySerializer
     */
    private $categorySerializer;

    /**
     * @var HintSerializer
     */
    private $hintSerializer;

    /**
     * @var ResourceContentSerializer
     */
    private $resourceContentSerializer;

    /**
     * @var ItemObjectSerializer
     */
    private $itemObjectSerializer;

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
     * @param CategorySerializer        $categorySerializer
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
     *     "categorySerializer"        = @DI\Inject("ujm_exo.serializer.category"),
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
        CategorySerializer $categorySerializer,
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
        $this->categorySerializer = $categorySerializer;
        $this->hintSerializer = $hintSerializer;
        $this->resourceContentSerializer = $resourceContentSerializer;
        $this->itemObjectSerializer = $itemObjectSerializer;
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Converts a Item into a JSON-encodable structure.
     *
     * @param Item  $question
     * @param array $options
     *
     * @return \stdClass
     */
    public function serialize($question, array $options = [])
    {
        // Serialize specific data for the item type
        $questionData = $this->serializeQuestionType($question, $options);

        if (1 === preg_match('#^application\/x\.[^/]+\+json$#', $question->getMimeType())) {
            $canEdit = $this->tokenStorage->getToken() && $this->tokenStorage->getToken()->getUser() instanceof User ?
                $this->container->get('ujm_exo.manager.item')->canEdit($question, $this->tokenStorage->getToken()->getUser()) :
                false;
            $rights = [
              'edit' => $canEdit,
            ];
            // Adds minimal information
            $this->mapEntityToObject([
                'id' => 'uuid',
                'autoId' => 'id',
                'type' => 'mimeType',
                'content' => 'content',
                'title' => 'title',
                'meta' => function (Item $question) use ($options) {
                    return $this->serializeMetadata($question, $options);
                },
                'score' => function (Item $question) {
                    return json_decode($question->getScoreRule());
                },
                'rights' => function (Item $question) use ($rights) {
                    return $rights;
                },
            ], $question, $questionData);

            // Adds full definition of the item
            if (!$this->hasOption(Transfer::MINIMAL, $options)) {
                $this->mapEntityToObject([
                    'description' => 'description',
                    'hints' => function (Item $question) use ($options) {
                        return $this->serializeHints($question, $options);
                    },
                    'objects' => function (Item $question) {
                        return $this->serializeObjects($question);
                    },
                    'resources' => function (Item $question) {
                        return $this->serializeResources($question);
                    },
                    'tags' => function (Item $question) {
                        return $this->serializeTags($question);
                    },
                ], $question, $questionData);

                // Adds item feedback
                if ($this->hasOption(Transfer::INCLUDE_SOLUTIONS, $options)) {
                    $this->mapEntityToObject([
                        'feedback' => 'feedback',
                    ], $question, $questionData);
                }
            }
        } else {
            $this->mapEntityToObject([
                'id' => 'uuid',
                'type' => 'mimeType',
                'title' => 'title',
                'meta' => function (Item $question) use ($options) {
                    return $this->serializeMetadata($question, $options);
                },
            ], $question, $questionData);

            // Adds full definition of the item
            if (!$this->hasOption(Transfer::MINIMAL, $options)) {
                $this->mapEntityToObject([
                    'description' => 'description',
                ], $question, $questionData);
            }
        }

        return $questionData;
    }

    /**
     * Converts raw data into a Item entity.
     *
     * @param \stdClass $data
     * @param Item      $item
     * @param array     $options
     *
     * @return Item
     */
    public function deserialize($data, $item = null, array $options = [])
    {
        if (!$this->hasOption(Transfer::NO_FETCH, $options) && empty($item) && !empty($data->id)) {
            // Loads the Item from DB if already exist
            $item = $this->om->getRepository('UJMExoBundle:Item\Item')->findOneBy([
                'uuid' => $data->id,
            ]);
        }

        $item = $item ?: new Item();
        $item->setUuid($data->id);

        // Sets the creator of the Item if not set
        $creator = $item->getCreator();
        if (empty($creator) || !($creator instanceof User)) {
            $token = $this->tokenStorage->getToken();
            if (!empty($token) && $token->getUser() instanceof User) {
                $item->setCreator($token->getUser());
            }
        }

        if (1 === preg_match('#^application\/x\.[^/]+\+json$#', $data->type)) {
            // question item
            // Map data to entity (dataProperty => entityProperty/function to call)
            $this->mapObjectToEntity([
                'type' => 'mimeType',
                'content' => 'content',
                'title' => 'title',
                'description' => 'description',
                'feedback' => 'feedback',
                'hints' => function (Item $item, \stdClass $data) use ($options) {
                    return $this->deserializeHints($item, $data->hints, $options);
                },
                'objects' => function (Item $item, \stdClass $data) use ($options) {
                    return $this->deserializeObjects($item, $data->objects, $options);
                },
                'resources' => function (Item $item, \stdClass $data) use ($options) {
                    return $this->deserializeResources($item, $data->resources, $options);
                },
                'meta' => function (Item $item, \stdClass $data) {
                    return $this->deserializeMetadata($item, $data->meta);
                },
                'score' => function (Item $item, \stdClass $data) {
                    $score = $this->sanitizeScore($data->score);
                    $item->setScoreRule(json_encode($score));
                },
            ], $data, $item);

            if (isset($data->tags)) {
                $this->deserializeTags($item, $data->tags, $options);
            }
        } else {
            // content item
            $this->mapObjectToEntity([
                'type' => 'mimeType',
                'title' => 'title',
                'description' => 'description',
            ], $data, $item);
        }

        $this->deserializeQuestionType($item, $data, $options);

        return $item;
    }

    /**
     * Serializes Item data specific to its type.
     * Forwards the serialization to the correct handler.
     *
     * @param Item  $question
     * @param array $options
     *
     * @return \stdClass
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
     * @param Item      $question
     * @param \stdClass $data
     * @param array     $options
     */
    private function deserializeQuestionType(Item $question, \stdClass $data, array $options = [])
    {
        $type = $this->itemDefinitions->getConvertedType($question->getMimeType());
        $definition = $this->itemDefinitions->get($type);

        // Deserialize item type data
        $type = $definition->deserializeQuestion($data, $question->getInteraction(), $options);
        $type->setQuestion($question);
    }

    /**
     * Serializes Item metadata.
     *
     * @param Item  $question
     * @param array $options
     *
     * @return \stdClass
     */
    private function serializeMetadata(Item $question, array $options = [])
    {
        $metadata = new \stdClass();

        $creator = $question->getCreator();

        $metadata->protectQuestion = $question->getProtectUpdate();
        $metadata->mandatory = $question->isMandatory();

        if (!empty($creator)) {
            $metadata->authors = [
                $this->userSerializer->serialize($creator, $options),
            ];
        }

        if ($question->getDateCreate()) {
            $metadata->created = $question->getDateCreate()->format('Y-m-d\TH:i:s');
        }

        if ($question->getDateModify()) {
            $metadata->updated = $question->getDateModify()->format('Y-m-d\TH:i:s');
        }

        if ($this->hasOption(Transfer::INCLUDE_ADMIN_META, $options)) {
            $metadata->model = $question->isModel();

            /** @var ExerciseRepository $exerciseRepo */
            $exerciseRepo = $this->om->getRepository('UJMExoBundle:Exercise');

            // Gets exercises that use this item
            $exercises = $exerciseRepo->findByQuestion($question);
            $metadata->usedBy = array_map(function (Exercise $exercise) {
                return $exercise->getUuid();
            }, $exercises);

            // Gets users who have access to this item
            $users = $this->om->getRepository('UJMExoBundle:Item\Shared')->findBy(['question' => $question]);
            $metadata->sharedWith = array_map(function (Shared $sharedQuestion) use ($options) {
                $shared = new \stdClass();
                $shared->adminRights = $sharedQuestion->hasAdminRights();
                $shared->user = $this->userSerializer->serialize($sharedQuestion->getUser(), $options);

                return $shared;
            }, $users);

            // Adds category
            if (!empty($question->getCategory())) {
                $metadata->category = $this->categorySerializer->serialize($question->getCategory(), $options);
            }
        }

        return $metadata;
    }

    /**
     * Deserializes Item metadata.
     *
     * @param Item      $question
     * @param \stdClass $metadata
     */
    public function deserializeMetadata(Item $question, \stdClass $metadata)
    {
        if (isset($metadata->model)) {
            $question->setModel($metadata->model);
        }

        if (isset($metadata->category)) {
            $category = $this->categorySerializer->deserialize($metadata->category);
            $question->setCategory($category);
        }

        if (isset($metadata->protectQuestion)) {
            $question->setProtectUpdate($metadata->protectQuestion);
        }

        if (isset($metadata->mandatory)) {
            $question->setMandatory($metadata->mandatory);
        }
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
                if ($entityHint->getUuid() === $hintData->id) {
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
        return array_map(function (ItemObject $object) use ($options) {
            return $this->itemObjectSerializer->serialize($object, $options);
        }, $question->getObjects()->toArray());
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

        foreach ($objects as $index => $objectData) {
            $existingObject = null;

            // Searches for an existing object entity.
            foreach ($objectEntities as $entityIndex => $entityObject) {
                /** @var ItemObject $entityObject */
                if ($entityObject->getUuid() === $objectData->id) {
                    $existingObject = $entityObject;
                    unset($objectEntities[$entityIndex]);
                    break;
                }
            }
            $toAdd = empty($existingObject);
            $itemObject = $this->itemObjectSerializer->deserialize($objectData, $existingObject, $options);
            $itemObject->setOrder($index);

            // Link object to item
            if ($toAdd) {
                $question->addObject($itemObject);
            }
        }

        // Remaining objects are no longer in the Item
        if (0 < count($objectEntities)) {
            foreach ($objectEntities as $objectToRemove) {
                $question->removeObject($objectToRemove);
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
                if ((string) $entityResource->getId() === $resourceData->id) {
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
     * @return \stdClass
     */
    private function sanitizeScore($score)
    {
        $sanitized = new \stdClass();

        $sanitized->type = $score->type;
        switch ($score->type) {
            case 'fixed':
                $sanitized->success = $score->success;
                $sanitized->failure = $score->failure;
                break;

            case 'manual':
                $sanitized->max = $score->max;
                break;
        }

        return $sanitized;
    }

    /**
     * Serializes Item tags.
     * Forwards the tag serialization to ItemTagSerializer.
     *
     * @param Item  $question
     * @param array $options
     *
     * @return array
     */
    private function serializeTags(Item $question)
    {
        $data = ['class' => 'UJM\ExoBundle\Entity\Item\Item', 'ids' => [$question->getId()]];
        $event = new GenericDataEvent($data);
        $this->eventDispatcher->dispatch('claroline_retrieve_used_tags_by_class_and_ids', $event);

        return $event->getResponse();
    }

    /**
     * Deserializes Item tags.
     *
     * @param Item  $question
     * @param array $tags
     */
    private function deserializeTags(Item $question, array $tags = [], array $options = [])
    {
        if ($this->hasOption(Transfer::PERSIST_TAG, $options)) {
            $data = [
              'tags' => $tags,
              'data' => [
                  [
                      'class' => 'UJM\ExoBundle\Entity\Item\Item',
                      'id' => $question->getId(),
                      'name' => $question->getTitle(),
                  ],
              ],
              'replace' => true,
          ];
            $event = new GenericDataEvent($data);

            if ($question->getUuid()) {
                $this->eventDispatcher->dispatch('claroline_tag_multiple_data', $event);
            }
        }
    }
}
