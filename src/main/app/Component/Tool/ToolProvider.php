<?php

namespace Claroline\AppBundle\Component\Tool;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Component\AbstractComponentProvider;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\ToolRights;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\ConfigureToolEvent;
use Claroline\CoreBundle\Event\Tool\ExportToolEvent;
use Claroline\CoreBundle\Event\Tool\ImportToolEvent;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\CoreBundle\Repository\Tool\OrderedToolRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Aggregates all the tools defined in the Claroline app.
 *
 * A tool MUST :
 *   - be declared as a symfony service and tagged with "claroline.component.tool".
 *   - implement the ToolInterface interface (or the AbstractTool class).
 */
class ToolProvider extends AbstractComponentProvider
{
    private OrderedToolRepository $orderedToolRepo;

    public function __construct(
        private readonly iterable $registeredTools,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly Crud $crud
    ) {
        $this->orderedToolRepo = $om->getRepository(OrderedTool::class);
    }

    final public static function getServiceTag(): string
    {
        return 'claroline.component.tool';
    }

    /**
     * Get the list of all the tools injected in the app by the current plugins.
     * It does not contain tools for disabled plugins.
     */
    protected function getRegisteredComponents(): iterable
    {
        return $this->registeredTools;
    }

    /**
     * Get the list of all implemented tools for a context.
     * It contains the tools from all the enabled plugins.
     */
    public function getAvailableTools(string $context, ContextSubjectInterface $contextSubject = null): array
    {
        $available = [];
        foreach ($this->getRegisteredComponents() as $toolComponent) {
            if ($toolComponent->supportsContext($context) && (empty($contextSubject) || $toolComponent->supportsSubject($contextSubject))) {
                $available[] = $toolComponent;
            }
        }

        return $available;
    }

    public function getEnabledTools(string $context, ContextSubjectInterface $contextSubject = null): array
    {
        return $this->orderedToolRepo->findByContext($context, $contextSubject ? $contextSubject->getContextIdentifier() : null);
    }

    public function isEnabled(string $toolName, string $context, ContextSubjectInterface $contextSubject = null): bool
    {
        return !empty($this->orderedToolRepo->findOneByNameAndContext($toolName, $context, $contextSubject ? $contextSubject->getContextIdentifier() : null));
    }

    public function getTool(string $toolName, string $context, ContextSubjectInterface $contextSubject = null): ?OrderedTool
    {
        /** @var ToolInterface $toolHandler */
        $toolHandler = $this->getComponent($toolName);

        if (!$toolHandler->supportsContext($context)) {
            throw new \Exception(sprintf('Tool "%s" does not support the context "%s". Check %s::supportsContext() for more info.', $toolName, $context, get_class($toolHandler)));
        }

        if ($contextSubject && !$toolHandler->supportsSubject($contextSubject)) {
            throw new \RuntimeException(sprintf('Tool "%s" does not support the context "%s(%s)". Check %s::supportsSubject() for more info.', $toolName, $context, $contextSubject->getContextIdentifier(), get_class($toolHandler)));
        }

        $orderedTool = $this->orderedToolRepo->findOneByNameAndContext($toolName, $context, $contextSubject ? $contextSubject->getContextIdentifier() : null);
        if (empty($orderedTool)) {
            // tool is not enabled in the context
            throw new \RuntimeException(sprintf('Tool "%s" is not enabled for the context "%s(%s)".', $toolName, $context, $contextSubject ? $contextSubject->getContextIdentifier() : ''));
        }

        return $orderedTool;
    }

    public function open(string $toolName, string $context, ContextSubjectInterface $contextSubject = null): ?array
    {
        /** @var ToolInterface $toolHandler */
        $toolHandler = $this->getComponent($toolName);

        // call handler open to grab custom data or execute side effects
        $openResponse = $toolHandler->open($context, $contextSubject) ?? [];

        // dispatch open event to let the app a tool has been opened
        // this is useful for side effects or to let others plugins integrates with the tool (e.g. IntegrationTool is extensible by plugins).
        $openEvent = new OpenToolEvent($toolName, $context, $contextSubject);
        $this->eventDispatcher->dispatch($openEvent, ToolEvents::OPEN);

        return array_merge([], $openEvent->getResponse(), $openResponse);
    }

    public function configure(string $toolName, string $context, ContextSubjectInterface $contextSubject = null, array $data = []): ?array
    {
        /** @var ToolInterface $toolHandler */
        $toolHandler = $this->getComponent($toolName);

        $configureResponse = $toolHandler->configure($context, $contextSubject, $data) ?? [];

        $configureEvent = new ConfigureToolEvent($toolName, $context, $contextSubject, $data);
        $this->eventDispatcher->dispatch($configureEvent, ToolEvents::CONFIGURE);

        return array_merge([], $configureEvent->getResponse(), $configureResponse);
    }

    public function import(string $toolName, string $context, ContextSubjectInterface $contextSubject = null, FileBag $fileBag = null, array $data = [], array $entities = []): array
    {
        /** @var ToolInterface $toolHandler */
        $toolHandler = $this->getComponent($toolName);

        // create new tool and link it to the context
        $orderedTool = new OrderedTool();
        $orderedTool->setContextName($context);
        if ($contextSubject) {
            $orderedTool->setContextId($contextSubject->getContextIdentifier());
        }

        // set tool config
        $this->crud->create($orderedTool, $data['orderedTool'], [SerializerInterface::REFRESH_UUID, Crud::NO_PERMISSIONS, Crud::THROW_EXCEPTION]);

        // set tool rights
        foreach ($data['rights'] as $rightsData) {
            if (empty($entities[$rightsData['role']['id']])) {
                continue;
            }

            $rights = new ToolRights();
            $rights->setOrderedTool($orderedTool);

            $this->crud->create($rights, array_merge($rightsData, [
                'role' => ['id' => $entities[$rightsData['role']['id']]->getUuid()],
            ]), [SerializerInterface::REFRESH_UUID, Crud::NO_PERMISSIONS, Crud::THROW_EXCEPTION]);
        }

        // call handler to let it manage custom tool data if any
        $toolEntities = $toolHandler->import($context, $contextSubject, $fileBag, $data['data'] ?? [], $entities);

        $importEvent = new ImportToolEvent($toolName, $context, $contextSubject, $fileBag, $data['data'] ?? [], $entities);
        $this->eventDispatcher->dispatch($importEvent, ToolEvents::IMPORT);

        $this->om->flush();

        return array_merge([], $entities, $toolEntities, $importEvent->getCreatedEntities());
    }

    public function export(string $toolName, string $context, ContextSubjectInterface $contextSubject = null, FileBag $fileBag = null): array
    {
        /** @var ToolInterface $toolHandler */
        $toolHandler = $this->getComponent($toolName);

        $orderedTool = $this->getTool($toolName, $context, $contextSubject);

        // call handler to let it return custom tool data if any
        $toolData = $toolHandler->export($context, $contextSubject, $fileBag);

        $exportEvent = new ExportToolEvent($toolName, $context, $contextSubject, $fileBag);
        $this->eventDispatcher->dispatch($exportEvent, ToolEvents::EXPORT);

        return [
            'name' => $toolName,
            'orderedTool' => $this->serializer->serialize($orderedTool, [SerializerInterface::SERIALIZE_TRANSFER]),
            'rights' => array_map(function (ToolRights $rights) {
                return $this->serializer->serialize($rights, [SerializerInterface::SERIALIZE_TRANSFER]);
            }, $orderedTool->getRights()->toArray()),
            'data' => array_merge([], $toolData, $exportEvent->getData()),
        ];
    }
}
