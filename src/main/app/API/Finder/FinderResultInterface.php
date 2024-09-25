<?php

namespace Claroline\AppBundle\API\Finder;

use Symfony\Component\HttpFoundation\StreamedJsonResponse;

/**
 * Represents the result of a Finder search.
 * Real DB queries are not done before calling one of the class methods.
 * Results are cached, calling multiple times class methods will not generate additional DB queries.
 */
interface FinderResultInterface
{
    public function count(): int;

    public function getItems(): iterable;

    public function toResponse(): StreamedJsonResponse;
}
