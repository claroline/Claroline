<?php

namespace Claroline\CoreBundle\Manager\Workspace\Transfer\Tools;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Tab\HomeTab;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Psr\Log\LoggerAwareInterface;

class Home implements ToolImporterInterface, LoggerAwareInterface
{
    use LoggableTrait;

    /**
     * WorkspaceSerializer constructor.
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
