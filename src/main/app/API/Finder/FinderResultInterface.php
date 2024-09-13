<?php

namespace Claroline\AppBundle\API\Finder;

interface FinderResultInterface
{
    public function count(): int;
    public function getItems(): iterable;
}
