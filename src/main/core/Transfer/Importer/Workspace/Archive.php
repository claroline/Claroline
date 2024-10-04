<?php

namespace Claroline\CoreBundle\Transfer\Importer\Workspace;

use Claroline\AppBundle\API\Crud;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\TransferBundle\Transfer\Importer\AbstractImporter;

class Archive extends AbstractImporter
{
    public function __construct(
        private readonly Crud $crud
    ) {
    }

    public function execute(array $data): array
    {
        if (empty($data[static::getAction()[0]])) {
            return [];
        }

        /** @var Workspace $object */
        $object = $this->crud->find(Workspace::class, $data[static::getAction()[0]]);

        if (!empty($object)) {
            $this->crud->replace($object, 'archived', true);

            return [
                'archive' => [[
                    'data' => $data,
                    'log' => static::getAction()[0].' archived.',
                ]],
            ];
        }

        return [];
    }

    public static function getAction(): array
    {
        return ['workspace', 'archive'];
    }

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        // this is so we don't show all properties. See ImportProvider and search $root
        return [static::getAction()[0] => Workspace::class];
    }
}
