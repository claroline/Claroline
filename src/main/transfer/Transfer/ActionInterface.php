<?php

namespace Claroline\TransferBundle\Transfer;

interface ActionInterface
{
    public function getAction(): array;

    public function supports(string $format, ?array $options = [], ?array $extra = []): bool;

    public function getSchema(?array $options = [], ?array $extra = []): array;

    public function getExtraDefinition(?array $options = [], ?array $extra = []): array;

    /**
     * Defines the number of rows which will be processed at once.
     *
     * - For import, it means the number of rows flushed at once.
     * - For export, it means the number of rows fetched from the DB and dumped to the file at once.
     */
    public function getBatchSize(): int;
}
