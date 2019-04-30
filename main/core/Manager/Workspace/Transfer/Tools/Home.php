<?php

namespace Claroline\CoreBundle\Manager\Workspace\Transfer\Tools;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Tab\HomeTab;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.transfer.home")
 */
class Home implements ToolImporterInterface
{
    use LoggableTrait;

    /**
     * WorkspaceSerializer constructor.
     *
     * @DI\InjectParams({
     *     "serializer"   = @DI\Inject("claroline.api.serializer"),
     *     "finder"       = @DI\Inject("claroline.api.finder"),
     *     "crud"         = @DI\Inject("claroline.api.crud")
     * })
     *

     * @param SerializerProvider $serializer
     */
    public function __construct(
          SerializerProvider $serializer,
          FinderProvider $finder,
          Crud $crud
      ) {
        $this->serializer = $serializer;
        $this->finder = $finder;
        $this->crud = $crud;
    }

    /**
     * @return array
     */
    public function serialize(Workspace $workspace, array $options): array
    {
        $tabs = $this->finder->search(
          HomeTab::class,
          ['filters' => ['workspace' => $workspace->getUuid()]],
          $options
        );

        // but why ? finder should never give you an empty row
        $tabs = array_filter($tabs['data'], function ($data) {
            return $data !== [];
        });

        return ['tabs' => $tabs];
    }

    public function prepareImport(array $orderedToolData, array $data): array
    {
        return $data;
    }

    public function deserialize(array $data, Workspace $workspace, array $options, FileBag $bag)
    {
        foreach ($data['tabs'] as $tab) {
            // do not update tabs set by the administration tool
            $new = $this->crud->create(HomeTab::class, $tab, $options);
            $new->setWorkspace($workspace);
        }
    }
}
