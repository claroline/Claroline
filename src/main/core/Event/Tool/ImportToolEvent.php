<?php

namespace Claroline\CoreBundle\Event\Tool;

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

class ImportToolEvent extends AbstractToolEvent
{
    /** @var FileBag */
    private $fileBag;

    /**
     * The serialized data to import.
     *
     * @var array
     */
    private $data;

    /**
     * The list of entities created by the import. Keys are the old UUIDs of the entities.
     *
     * @var array
     */
    private $entities;

    public function __construct(
        string $toolName,
        string $context,
        ?Workspace $workspace = null,
        ?FileBag $fileBag = null,
        ?array $data = [],
        ?array $entities = []
    ) {
        parent::__construct($toolName, $context, $workspace);

        $this->fileBag = $fileBag ?? new FileBag();
        $this->data = $data;
        $this->entities = $entities;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getFileBag(): FileBag
    {
        return $this->fileBag;
    }

    public function getFile(string $fileIdentifier): ?string
    {
        return $this->fileBag->get($fileIdentifier);
    }

    public function getCreatedEntities(): array
    {
        return $this->entities;
    }

    public function getCreatedEntity(string $oldUuid)
    {
        if (!empty($this->entities[$oldUuid])) {
            return $this->entities[$oldUuid];
        }

        return null;
    }

    public function addCreatedEntity(string $oldUuid, $entity)
    {
        $this->entities[$oldUuid] = $entity;
    }
}
