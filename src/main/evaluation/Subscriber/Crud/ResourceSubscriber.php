<?php

namespace Claroline\EvaluationBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Event\Crud\CopyEvent;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\ResourceEvents;
use Claroline\CoreBundle\Event\Resource\CreateResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Event\Resource\UpdateResourceEvent;
use Claroline\EvaluationBundle\Entity\Parameters\ResourceParameters;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ResourceSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly Crud $crud,
        private readonly ResourceEvaluationManager $evaluationManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ResourceEvents::getEventName(ResourceEvents::OPEN) => 'open',
            ResourceEvents::getEventName(ResourceEvents::CREATE) => 'postCreate',
            ResourceEvents::getEventName(ResourceEvents::UPDATE) => 'postUpdate',
            CrudEvents::getEventName(CrudEvents::POST_COPY, ResourceNode::class) => 'postCopy',
        ];
    }

    public function open(LoadResourceEvent $event): void
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        $resourceNode = $event->getResourceNode();

        $evaluationParameters = $this->evaluationManager->getParameters($resourceNode);

        // get or initialize the current user evaluation if the resource enables it
        $userEvaluation = null;
        if (!empty($evaluationParameters) && $user instanceof User) {
            $userEvaluation = $this->evaluationManager->getUserEvaluation($resourceNode, $user);
        }

        $event->addData([
            'evaluation' => $evaluationParameters ?
                $this->serializer->serialize($evaluationParameters, [SerializerInterface::SERIALIZE_MINIMAL]) :
                null,
            'userEvaluation' => $userEvaluation ?
                $this->serializer->serialize($userEvaluation, [SerializerInterface::SERIALIZE_MINIMAL]) :
                null,
        ]);
    }

    /**
     * Create evaluation parameters for the created resource if needed.
     */
    public function postCreate(CreateResourceEvent $event): void
    {
        $resourceNode = $event->getResourceNode();

        $data = $event->getData();
        if (array_key_exists('evaluation', $data)) {
            $this->manageEvaluationParameters($resourceNode, $data['evaluation']);
        }
    }

    /**
     * Update evaluation parameters for the updated resource if needed.
     */
    public function postUpdate(UpdateResourceEvent $event): void
    {
        $resource = $event->getResourceNode();

        $data = $event->getData();
        if (array_key_exists('evaluation', $data)) {
            $evaluationParameters = $this->manageEvaluationParameters($resource, $data['evaluation']);
            $event->addResponse([
                'evaluation' => $evaluationParameters ?
                    $this->serializer->serialize($evaluationParameters, [SerializerInterface::SERIALIZE_MINIMAL]) :
                    null,
            ]);
        }
    }

    public function postCopy(CopyEvent $event): void
    {
        $options = $event->getOptions();
        /** @var ResourceNode $originalResource */
        $originalResource = $event->getObject();
        /** @var ResourceNode $newResource */
        $newResource = $event->getCopy();

        $this->om->startFlushSuite();

        /** @var ResourceParameters $parameters */
        $parameters = $this->om->getRepository(ResourceParameters::class)->findOneBy(['resource' => $originalResource]);
        if ($parameters) {
            $newParameters = $this->crud->copy($parameters, $options);
            $newParameters->setResource($newResource);
        }

        $this->om->endFlushSuite();
    }

    private function manageEvaluationParameters(ResourceNode $resource, ?array $evaluationParameters = null): ?ResourceParameters
    {
        $parameters = $this->om->getRepository(ResourceParameters::class)->findOneBy(['resource' => $resource]);
        if (empty($evaluationParameters)) {
            $this->crud->delete($parameters);
        } else {
            if ($parameters) {
                $this->crud->update($parameters, $evaluationParameters);
            } else {
                $parameters = new ResourceParameters();
                $parameters->setResource($resource);
                $this->crud->create($parameters, $evaluationParameters);
            }
        }

        return $parameters;
    }
}
