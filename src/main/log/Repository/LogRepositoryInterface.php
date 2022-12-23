<?php

namespace Claroline\LogBundle\Repository;

use Doctrine\Common\Collections\Collection;

interface LogRepositoryInterface
{
    public function findLogsOlderThan(\DateTimeInterface $date): Collection;
}