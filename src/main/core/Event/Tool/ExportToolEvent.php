<?php

namespace Claroline\CoreBundle\Event\Tool;

use Claroline\AppBundle\API\Utils\ArrayUtils;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

class ExportToolEvent extends AbstractToolEvent
{
    private FileBag $fileBag;

    private array $data = [];

    public function __construct(
        string $toolName,
        string $context,
        Workspace $workspace = null,
        FileBag $fileBag = null
    ) {
        parent::__construct($toolName, $context, $workspace);

        $this->fileBag = $fileBag ?? new FileBag();
    }

    public function setData(?array $data = []): void
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function addFile($path, $file): void
    {
        $this->fileBag->add($path, $file);
    }

    public function getFileBag(): FileBag
    {
        return $this->fileBag;
    }

    public function overwrite($key, $value): void
    {
        ArrayUtils::set($this->data, $key, $value);
    }
}
