<?php

namespace Claroline\TransferBundle\Transfer\Exporter;

use Claroline\TransferBundle\Transfer\ActionInterface;

interface ExporterInterface extends ActionInterface
{
    public function execute(int $batchNumber, ?array $options = [], ?array $extra = []): array;
}
