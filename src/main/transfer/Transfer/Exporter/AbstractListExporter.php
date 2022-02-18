<?php

namespace Claroline\TransferBundle\Transfer\Exporter;

use Claroline\AppBundle\API\Crud;

abstract class AbstractListExporter extends AbstractExporter
{
    /** @var Crud */
    protected $crud;

    abstract protected static function getClass(): string;

    public function setCrud(Crud $crud)
    {
        $this->crud = $crud;
    }

    public function execute(?array $options = [], ?array $extra = []): array
    {
        $query = [];
        if (!empty($extra) && !empty($extra['workspace'])) {
            $query = ['hiddenFilters' => ['workspace' => $extra['workspace']['id']]];
        }

        $list = $this->crud->list(static::getClass(), $query, $this->getOptions());

        return $list['data'];
    }

    protected function getOptions(): array
    {
        return [];
    }
}
