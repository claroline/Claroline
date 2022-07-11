<?php

namespace Claroline\CoreBundle\Transfer\Importer\Workspace;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\TransferBundle\Transfer\Importer\AbstractImporter;

class Unarchive extends AbstractImporter
{
    /** @var ObjectManager */
    private $om;
    /** @var WorkspaceManager */
    private $manager;

    public function __construct(
        ObjectManager $om,
        WorkspaceManager $manager
    ) {
        $this->om = $om;
        $this->manager = $manager;
    }

    public function execute(array $data, &$successData = []): array
    {
        if (empty($data[static::getAction()[0]])) {
            return [];
        }

        /** @var Workspace $object */
        $object = $this->om->getObject($data[static::getAction()[0]], Workspace::class, array_keys($data[static::getAction()[0]]));

        if (!empty($object)) {
            $this->manager->unarchive($object);

            return [
                'unarchive' => [[
                    'data' => $data,
                    'log' => static::getAction()[0].' unarchived.',
                ]],
            ];
        }

        return [];
    }

    public static function getAction(): array
    {
        return ['workspace', 'unarchive'];
    }

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        //this is so we don't show all properties. See ImportProvider and search $root
        return [static::getAction()[0] => Workspace::class];
    }
}
