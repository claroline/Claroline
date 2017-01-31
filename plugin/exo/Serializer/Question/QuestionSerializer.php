<?php

namespace UJM\ExoBundle\Serializer\Question;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Question\Hint;
use UJM\ExoBundle\Entity\Question\Question;
use UJM\ExoBundle\Entity\Question\QuestionObject;
use UJM\ExoBundle\Entity\Question\QuestionResource;
use UJM\ExoBundle\Entity\Question\Shared;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Question\QuestionDefinitionsCollection;
use UJM\ExoBundle\Library\Serializer\AbstractSerializer;
use UJM\ExoBundle\Repository\ExerciseRepository;
use UJM\ExoBundle\Serializer\Content\ResourceContentSerializer;
use UJM\ExoBundle\Serializer\UserSerializer;

/**
 * Serializer for question data.
 *
 * @DI\Service("ujm_exo.serializer.question")
 */
class QuestionSerializer extends AbstractSerializer
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
     * @var QuestionDefinitionsCollection
     */
    private $questionDefinitions;

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
     * QuestionSerializer constructor.
     *
     * @param ObjectManager                 $om
     * @param TokenStorageInterface         $tokenStorage
     * @param QuestionDefinitionsCollection $questionDefinitions
     * @param UserSerializer                $userSerializer
     * @param CategorySerializer            $categorySerializer
     * @param HintSerializer                $hintSerializer
     * @param ResourceContentSerializer     $resourceContentSerializer
     *
     * @DI\InjectParams({
     *     "om"                        = @DI\Inject("claroline.persistence.object_manager"),
     *     "tokenStorage"              = @DI\Inject("security.token_storage"),
     *     "questionDefinitions"       = @DI\Inject("ujm_exo.collection.question_definitions"),
     *     "userSerializer"            = @DI\Inject("ujm_exo.serializer.user"),
     *     "categorySerializer"        = @DI\Inject("ujm_exo.serializer.category"),
     *     "hintSerializer"            = @DI\Inject("ujm_exo.serializer.hint"),
     *     "resourceContentSerializer" = @DI\Inject("ujm_exo.serializer.resource_content")
     * })
     */
    public function __construct(
        ObjectManager $om,
        TokenStorageInterface $tokenStorage,
        QuestionDefinitionsCollection $questionDefinitions,
        UserSerializer $userSerializer,
        CategorySerializer $categorySerializer,
        HintSerializer $hintSerializer,
        ResourceContentSerializer $resourceContentSerializer)
    {
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;
        $this->questionDefinitions = $questionDefinitions;
        $this->userSerializer = $userSerializer;
        $this->categorySerializer = $categorySerializer;
        $this->hintSerializer = $hintSerializer;
        $this->resourceContentSerializer = $resourceContentSerializer;
    }

    /**
     * Converts a Question into a JSON-encodable structure.
     *
     * @param Question $question
     * @param array    $options
     *
     * @return \stdClass
     */
    public function serialize($question, array $options = [])
    {
        // Serialize specific data for the question type
        $questionData = $this->serializeQuestionType($question, $options);

        // Adds minimal information
        $this->mapEntityToObject([
            'id' => 'uuid',
            'type' => 'mimeType',
            'content' => 'content',
            'title' => 'title',
            'meta' => function (Question $question) use ($options) {
                return $this->serializeMetadata($question, $options);
            },
            'score' => function (Question $question) {
                return json_decode($question->getScoreRule());
            },
        ], $question, $questionData);

        // Adds full definition of the question
        if (!$this->hasOption(Transfer::MINIMAL, $options)) {
            $this->mapEntityToObject([
                'description' => 'description',
                'hints' => function (Question $question) use ($options) {
                    return $this->serializeHints($question, $options);
                },
                'objects' => function (Question $question) {
                    return $this->serializeObjects($question);
                },
                'resources' => function (Question $question) {
                    return $this->serializeResources($question);
                },
            ], $question, $questionData);

            // Adds question feedback
            if (!$this->hasOption(Transfer::INCLUDE_SOLUTIONS, $options)) {
                $this->mapEntityToObject([
                    'feedback' => 'feedback',
                ], $question, $questionData);
            }
        }

        return $questionData;
    }

    /**
     * Converts raw data into a Question entity.
     *
     * @param \stdClass $data
     * @param Question  $question
     * @param array     $options
     *
     * @return Question
     */
    public function deserialize($data, $question = null, array $options = [])
    {
        if (empty($question)) {
            // Loads the Question from DB if already exist
            if (!empty($data->id)) {
                $question = $this->om->getRepository('UJMExoBundle:Question\Question')->findOneBy([
                    'uuid' => $data->id,
                ]);
            }

            if (empty($question)) {
                // Question not exist
                $question = new Question();
            }
        }

        // Force client ID if needed
        if (!in_array(Transfer::USE_SERVER_IDS, $options)) {
            $question->setUuid($data->id);
        }

        // Map data to entity (dataProperty => entityProperty/function to call)
        $this->mapObjectToEntity([
            'type' => 'mimeType',
            'content' => 'content',
            'title' => 'title',
            'description' => 'description',
            'hints' => function (Question $question, \stdClass $data) use ($options) {
                return $this->deserializeHints($question, $data->hints, $options);
            },
            'objects' => function (Question $question, \stdClass $data) use ($options) {
                return $this->deserializeObjects($question, $data->objects, $options);
            },
            'resources' => function (Question $question, \stdClass $data) use ($options) {
                return $this->deserializeResources($question, $data->resources, $options);
            },
            'meta' => function (Question $question, \stdClass $data) {
                return $this->deserializeMetadata($question, $data->meta);
            },
            'score' => function (Question $question, \stdClass $data) {
                $score = $this->sanitizeScore($data->score);
                $question->setScoreRule(json_encode($score));
            },
        ], $data, $question);

        $this->deserializeQuestionType($question, $data, $options);

        return $question;
    }

    /**
     * Serializes Question data specific to its type.
     * Forwards the serialization to the correct handler.
     *
     * @param Question $question
     * @param array    $options
     *
     * @return \stdClass
     */
    private function serializeQuestionType(Question $question, array $options = [])
    {
        $definition = $this->questionDefinitions->get($question->getMimeType());

        return $definition->serializeQuestion($question->getInteraction(), $options);
    }

    /**
     * Deserializes Question data specific to its type.
     * Forwards the serialization to the correct handler.
     *
     * @param Question  $question
     * @param \stdClass $data
     * @param array     $options
     */
    private function deserializeQuestionType(Question $question, \stdClass $data, array $options = [])
    {
        $definition = $this->questionDefinitions->get($question->getMimeType());

        // Deserialize question type data
        $type = $definition->deserializeQuestion($data, $question->getInteraction(), $options);
        $type->setQuestion($question);
    }

    /**
     * Serializes Question metadata.
     *
     * @param Question $question
     * @param array    $options
     *
     * @return \stdClass
     */
    private function serializeMetadata(Question $question, array $options = [])
    {
        $metadata = new \stdClass();

        $creator = $question->getCreator();
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

            // Gets exercises that use this question
            $exercises = $exerciseRepo->findByQuestion($question);
            $metadata->usedBy = array_map(function (Exercise $exercise) {
                return $exercise->getUuid();
            }, $exercises);

            // Gets users who have access to this question
            $users = $this->om->getRepository('UJMExoBundle:Question\Shared')->findBy(['question' => $question]);
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
     * Deserializes Question metadata.
     *
     * @param Question  $question
     * @param \stdClass $metadata
     */
    public function deserializeMetadata(Question $question, \stdClass $metadata)
    {
        if (isset($metadata->model)) {
            $question->setModel($metadata->model);
        }

        // Sets the creator of the Question if not set
        $creator = $question->getCreator();
        if (empty($creator) || !($creator instanceof User)) {
            $token = $this->tokenStorage->getToken();
            if (!empty($token) && $token->getUser() instanceof User) {
                $question->setCreator($token->getUser());
            }
        }

        if (isset($metadata->category)) {
            $category = $this->categorySerializer->deserialize($metadata->category);
            $question->setCategory($category);
        }
    }

    /**
     * Serializes Question hints.
     * Forwards the hint serialization to HintSerializer.
     *
     * @param Question $question
     * @param array    $options
     *
     * @return array
     */
    private function serializeHints(Question $question, array $options = [])
    {
        return array_map(function (Hint $hint) use ($options) {
            return $this->hintSerializer->serialize($hint, $options);
        }, $question->getHints()->toArray());
    }

    /**
     * Deserializes Question hints.
     * Forwards the hint deserialization to HintSerializer.
     *
     * @param Question $question
     * @param array    $hints
     * @param array    $options
     */
    private function deserializeHints(Question $question, array $hints = [], array $options = [])
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
     * Serializes Question objects.
     * Forwards the object serialization to ResourceContentSerializer.
     *
     * @param Question $question
     * @param array    $options
     *
     * @return array
     */
    private function serializeObjects(Question $question, array $options = [])
    {
        return array_map(function (QuestionObject $object) use ($options) {
            return $this->resourceContentSerializer->serialize($object->getResourceNode(), $options);
        }, $question->getObjects()->toArray());
    }

    /**
     * Deserializes Question objects.
     *
     * @param Question $question
     * @param array    $objects
     * @param array    $options
     */
    private function deserializeObjects(Question $question, array $objects = [], array $options = [])
    {
        $objectEntities = $question->getObjects()->toArray();

        foreach ($objects as $objectData) {
            $existingObject = null;

            // Searches for an existing object entity.
            foreach ($objectEntities as $entityIndex => $entityObject) {
                /** @var QuestionObject $entityObject */
                if ((string) $entityObject->getId() === $objectData->id) {
                    $existingObject = $entityObject;
                    unset($objectEntities[$entityIndex]);
                    break;
                }
            }

            // Link object to question
            if (empty($existingObject)) {
                $node = $this->resourceContentSerializer->deserialize($objectData, $existingObject, $options);
                if ($node) {
                    $question->addObject($node);
                }
            }
        }

        // Remaining objects are no longer in the Question
        if (0 < count($objectEntities)) {
            foreach ($objectEntities as $objectToRemove) {
                $question->removeObject($objectToRemove);
            }
        }
    }

    /**
     * Serializes Question resources.
     * Forwards the resource serialization to ResourceContentSerializer.
     *
     * @param Question $question
     * @param array    $options
     *
     * @return array
     */
    private function serializeResources(Question $question, array $options = [])
    {
        return array_map(function (QuestionResource $resource) use ($options) {
            return $this->resourceContentSerializer->serialize($resource->getResourceNode(), $options);
        }, $question->getResources()->toArray());
    }

    /**
     * Deserializes Question resources.
     *
     * @param Question $question
     * @param array    $resources
     * @param array    $options
     */
    private function deserializeResources(Question $question, array $resources = [], array $options = [])
    {
        $resourceEntities = $question->getResources()->toArray();

        foreach ($resources as $resourceData) {
            $existingResource = null;

            // Searches for an existing resource entity.
            foreach ($resourceEntities as $entityIndex => $entityResource) {
                /** @var QuestionResource $entityResource */
                if ((string) $entityResource->getId() === $resourceData->id) {
                    $existingResource = $entityResource;
                    unset($resourceEntities[$entityIndex]);
                    break;
                }
            }

            // Link resource to question
            if (empty($existingResource)) {
                $node = $this->resourceContentSerializer->deserialize($resourceData, $existingResource, $options);
                if ($node) {
                    $question->addResource($node);
                }
            }
        }

        // Remaining resources are no longer in the Question
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
}
