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
        $this->crud->update(static::getClass(), $data, $this->getOptions());

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
