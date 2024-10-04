<?php

namespace Claroline\CoreBundle\Transfer\Importer\Workspace;

use Claroline\AppBundle\API\Crud;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\TransferBundle\Transfer\Importer\AbstractImporter;

class Unarchive extends AbstractImporter
{
    public function __construct(
        private readonly Crud $crud
    ) {
    }

    public function execute(array $data, &$successData = []): array
    {
        if (empty($data[static::getAction()[0]])) {
            return [];
        }

        /** @var Workspace $object */
        $object = $this->crud->find(Workspace::class, $data[static::getAction()[0]]);

        if (!empty($object)) {
            $this->crud->replace($object, 'archived', false);

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
        // this is so we don't show all properties. See ImportProvider and search $root
        return [static::getAction()[0] => Workspace::class];
    }
}
