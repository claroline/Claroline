<?php

namespace Claroline\CoreBundle\Component\Resource;

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Component\ComponentInterface;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

interface ResourceInterface extends ComponentInterface
{
    public function read(AbstractResource $resource, bool $embedded = false): ?array;

    /**
     * Embed the resource inside html texts.
     */
    public function embed(AbstractResource $resource): string;

    public function download(AbstractResource $resource): string;

    public function create(AbstractResource $resource, array $data): void;

    public function update(AbstractResource $resource, array $data): void;

    public function delete(AbstractResource $resource, bool $softDelete = true): bool;

    public function copy(AbstractResource $original, AbstractResource $copy): void;

    public function export(AbstractResource $resource, FileBag $fileBag): ?array;

    /**
     * Import a resource inside the platform. Only possible through Workspace import.
     */
    public function import(AbstractResource $resource, FileBag $fileBag, array $data = []): void;
}
