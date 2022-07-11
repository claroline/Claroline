<?php

namespace Claroline\TransferBundle\Transfer\Importer;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Persistence\ObjectManager;

abstract class AbstractCollectionImporter extends AbstractImporter
{
    /** @var Crud */
    protected $crud;
    /** @var ObjectManager */
    protected $om;

    abstract protected static function getClass(): string;

    abstract protected static function getCollectionClass(): string;

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
        $parent = $this->om->getObject($data[static::getAction()[0]], static::getClass(), array_keys($data[static::getAction()[0]]));
        if (!$parent) {
            throw new \Exception(sprintf('%s does not exists', ucfirst(static::getAction()[0])));
        }

        $toAdd = $this->om->getObject($data[static::getCollectionAlias()], static::getCollectionClass(), array_keys($data[static::getCollectionAlias()]));
        if (!$toAdd) {
            throw new \Exception(sprintf('%s does not exists', ucfirst(static::getCollectionAlias())));
        }

        $this->crud->patch($parent, static::getCollectionAlias(), Crud::COLLECTION_ADD, [$toAdd], $this->getOptions());

        return [
            static::getAction()[1] => [[
                'data' => $data,
                'log' => sprintf('%s registered to %s.', static::getCollectionAlias(), static::getAction()[0]),
            ]],
        ];
    }

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        return [
            static::getAction()[0] => static::getClass(),
            static::getCollectionAlias() => static::getCollectionClass(),
        ];
    }

    protected function getOptions(): array
    {
        return [];
    }

    private static function getCollectionAction(): string
    {
        $action = static::getAction()[1];

        $collectionAction = null;
        if (-1 !== strpos($action, Crud::COLLECTION_ADD)) {
            $collectionAction = Crud::COLLECTION_ADD;
        } elseif (-1 !== strpos($action, Crud::COLLECTION_REMOVE)) {
            $collectionAction = Crud::COLLECTION_REMOVE;
        } else {
            throw new \Exception('Try to execute an unknown collection action.');
        }

        return $collectionAction;
    }

    private static function getCollectionAlias(): string
    {
        $action = static::getAction()[1];
        $collectionAction = static::getCollectionAction();

        return str_replace($collectionAction.'_', '', $action);
    }
}
