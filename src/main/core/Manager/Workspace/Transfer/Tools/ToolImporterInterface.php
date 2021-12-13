<?php

namespace Claroline\CoreBundle\Manager\Workspace\Transfer\Tools;

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

interface ToolImporterInterface
{
    public function serialize(Workspace $workspace, array $options): array;

    public function deserialize(array $data, Workspace $workspace, array $options, array $newEntities, FileBag $bag): array;

    public function prepareImport(array $orderedToolData, array $data): array;
}
