<?php

namespace Claroline\TransferBundle\Transfer\Importer;

use Claroline\TransferBundle\Transfer\ActionInterface;

interface ImporterInterface extends ActionInterface
{
    public function execute(array $data): array;
}
