<?php

namespace Claroline\TransferBundle\Transfer\Importer;

use Claroline\AppBundle\API\Crud;

abstract class AbstractUpdateImporter extends AbstractImporter
{
    /** @var Crud */
    protected $crud;

    abstract protected static function getClass(): string;

    public function setCrud(Crud $crud)
    {
        $this->crud = $crud;
    }

    public function execute(array $data): array
    {
        $toUpdate = $this->crud->find(static::getClass(), $data);
        if (!$toUpdate) {
            // we need to check the object exists first because the Crud::update will generate a new one if not.
            // we don't want updates actions accidentally generate new entities (there is the CreateOrUpdateImporter for that).
            return [
                'update' => [[
                    'data' => $data,
                    'log' => $this->getAction()[0].' not found.',
                ]],
            ];
        }

        $this->crud->update($toUpdate, $data, $this->getOptions());

        return [
            'update' => [[
                'data' => $data,
                'log' => $this->getAction()[0].' updated.',
            ]],
        ];
    }

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        return ['$root' => static::getClass()];
    }

    public function getMode()
    {
        return self::MODE_UPDATE;
    }

    protected function getOptions(): array
    {
        return [];
    }
}
