<?php

namespace Claroline\TransferBundle\Transfer\Exporter;

use Claroline\TransferBundle\Transfer\ActionInterface;

interface ExporterInterface extends ActionInterface
{
    public function execute(?array $options = [], ?array $extra = []): array;
}
