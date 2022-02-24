<?php

namespace Claroline\TransferBundle\Transfer\Importer;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Persistence\ObjectManager;

abstract class AbstractDeleteImporter extends AbstractImporter
{
    /** @var ObjectManager */
    protected $om;
    /** @var Crud */
    protected $crud;

    abstract protected static function getClass(): string;

    public function setCrud(Crud $crud)
    {
        $this->crud = $crud;
    }

    public function setObjectManager(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function execute(array $data): array
    {
        if (empty($data[$this->getAction()[0]])) {
            return [];
        }

        $object = $this->om->getObject(
            $data[$this->getAction()[0]],
            static::getClass(),
            array_keys($data[$this->getAction()[0]])
        );

        if (!empty($object)) {
            return [
                'delete' => [[
                    'data' => $data,
                    'log' => $this->getAction()[0].' not found.',
                ]],
            ];
        }

        $this->crud->delete($object, $this->getOptions());

        return [
            'delete' => [[
                'data' => $data,
                'log' => $this->getAction()[0].' deleted.',
            ]],
        ];
    }

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        //this is so we don't show all properties. See ImportProvider and search $root
        return [$this->getAction()[0] => static::getClass()];
    }

    protected function getOptions(): array
    {
        return [];
    }
}
