<?php

namespace Claroline\TransferBundle\Transfer\Importer;

use Claroline\AppBundle\API\Crud;
use Claroline\TransferBundle\Transfer\ImportProvider;

abstract class AbstractCreateOrUpdateImporter extends AbstractImporter
{
    protected Crud $crud;
    protected ImportProvider $transfer;

    abstract protected static function getClass(): string;

    /**
     * @internal only used by DI
     */
    public function setCrud(Crud $crud): void
    {
        $this->crud = $crud;
    }

    /**
     * @internal only used by DI
     */
    public function setTransfer(ImportProvider $transfer): void
    {
        $this->transfer = $transfer;
    }

    public function execute(array $data): array
    {
        $serializedClass = static::getAction()[0];

        $object = $this->crud->find(static::getClass(), $data);
        if (empty($object)) {
            // fire the creation action
            return $this->transfer->getAction($serializedClass.'_'.self::MODE_CREATE)->execute($data);
        }

        // fire the update action
        return $this->transfer->getAction($serializedClass.'_'.self::MODE_UPDATE)->execute($data);
    }

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        return ['$root' => static::getClass()];
    }
}
