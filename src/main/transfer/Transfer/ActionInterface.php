<?php

namespace Claroline\TransferBundle\Transfer;

interface ActionInterface
{
    public function getAction(): array;

    public function supports(string $format, ?array $options = [], ?array $extra = []): bool;

    public function getExtraDefinition(?array $options = [], ?array $extra = []): array;
}
