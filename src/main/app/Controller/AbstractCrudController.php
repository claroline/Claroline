<?php

namespace Claroline\AppBundle\Controller;

use Claroline\AppBundle\API\Controller\CreateAction;
use Claroline\AppBundle\API\Controller\DeleteAction;
use Claroline\AppBundle\API\Controller\GetAction;
use Claroline\AppBundle\API\Controller\ListAction;
use Claroline\AppBundle\API\Controller\UpdateAction;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;

abstract class AbstractCrudController
{
    use RequestDecoderTrait;
    use GetAction;
    use ListAction;
    use CreateAction;
    use UpdateAction;
    use DeleteAction;

    protected ObjectManager $om;
    protected SerializerProvider $serializer;
    protected Crud $crud;

    /**
     * Get the name of the managed entity.
     */
    abstract public static function getName(): string;

    /**
     * Get the name of the managed entity.
     */
    abstract public static function getClass(): string;

    public function setSerializer(SerializerProvider $serializer): void
    {
        $this->serializer = $serializer;
    }

    protected function getSerializer(): SerializerProvider
    {
        return $this->serializer;
    }

    public function setCrud(Crud $crud): void
    {
        $this->crud = $crud;
    }

    protected function getCrud(): Crud
    {
        return $this->crud;
    }

    public function setObjectManager(ObjectManager $om): void
    {
        $this->om = $om;
    }

    protected function getObjectManager(): ObjectManager
    {
        return $this->om;
    }

    public static function getOptions(): array
    {
        return [
            'get' => [],
            'list' => [Options::SERIALIZE_LIST],
            'create' => [],
            'update' => [],
        ];
    }

    protected function getDefaultHiddenFilters(): array
    {
        return [];
    }

    public function getIgnore(): array
    {
        return [];
    }
}
