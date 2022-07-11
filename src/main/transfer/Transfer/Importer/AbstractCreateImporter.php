<?php

namespace Claroline\TransferBundle\Transfer\Importer;

use Claroline\AppBundle\API\Crud;

abstract class AbstractCreateImporter extends AbstractImporter
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
        $this->crud->create(static::getClass(), $data, $this->getOptions());

        return [
            'create' => [[
                'data' => $data,
                'log' => static::getAction()[0].' created.',
            ]],
        ];
    }

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        return ['$root' => static::getClass()];
    }

    public function getMode()
    {
        return self::MODE_CREATE;
    }

    protected function getOptions(): array
    {
        return [];
    }
}
