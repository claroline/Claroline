<?php

namespace Claroline\AppBundle\Component\DataSource;

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
 * Aggregates all the data sources defined in the Claroline app.
 *
 * A data source MUST :
 *   - be declared as a symfony service and tagged with "claroline.component.data_source".
 *   - implement the DataSourceInterface interface (or the AbstractDataSource class in most cases).
 */
class DataSourceProvider extends AbstractComponentProvider
{
    public function __construct(
        private readonly iterable $registeredDataSources
    ) {
    }

    final public static function getServiceTag(): string
    {
        return 'claroline.component.data_source';
    }

    /**
     * Get the list of all the data sources injected in the app by the current plugins.
     * It does not contain data sources for disabled plugins.
     */
    protected function getRegisteredComponents(): iterable
    {
        return $this->registeredDataSources;
    }

    /**
     * Get the list of all implemented sources for a context.
     * It contains the sources from all the enabled plugins.
     */
    public function getAvailableSources(string $context, ContextSubjectInterface $contextSubject = null): array
    {
        $available = [];
        foreach ($this->getRegisteredComponents() as $toolComponent) {
            if ($toolComponent->supportsContext($context) && (empty($contextSubject) || $toolComponent->supportsSubject($contextSubject))) {
                $available[] = $toolComponent;
            }
        }

        return $available;
    }

    public function getDataSource(string $name, string $context, ContextSubjectInterface $contextSubject = null)
    {

    }
}
