<?php

namespace Claroline\HomeBundle\Manager\Workspace\Transfer;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Workspace\Transfer\Tools\ToolImporterInterface;
use Claroline\HomeBundle\Entity\HomeTab;
use Claroline\HomeBundle\Manager\HomeManager;
use Psr\Log\LoggerAwareInterface;

class Home implements ToolImporterInterface, LoggerAwareInterface
{
    use LoggableTrait;

    /** @var SerializerProvider */
    private $serializer;
    /** @var FinderProvider */
    private $finder;
    /** @var Crud */
    private $crud;
    /** @var HomeManager */
    private $manager;

    public function __construct(
        SerializerProvider $serializer,
        FinderProvider $finder,
        Crud $crud,
        HomeManager $manager
    ) {
        $this->serializer = $serializer;
        $this->finder = $finder;
        $this->crud = $crud;
        $this->manager = $manager;
    }

    public function serialize(Workspace $workspace, array $options): array
    {
        return [
            'tabs' => $this->manager->getWorkspaceTabs($workspace, $options),
        ];
    }

    public function prepareImport(array $orderedToolData, array $data): array
    {
        return $data;
    }

    public function deserialize(array $data, Workspace $workspace, array $options, array $newEntities, FileBag $bag): array
    {
        $createdTabs = [];

        foreach ($data['tabs'] as $tab) {
            if (isset($tab['workspace'])) {
                unset($tab['workspace']);
            }
            $new = new HomeTab();
            $new->setWorkspace($workspace);

            $this->crud->create($new, $tab, $options);

            $createdTabs[$tab['id']] = $new;
        }

        return $createdTabs;
    }
}
