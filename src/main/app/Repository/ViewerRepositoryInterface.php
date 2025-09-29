<?php

namespace Claroline\AppBundle\Repository;

use Claroline\AppBundle\Entity\AbstractUserView;
use Claroline\AppBundle\Entity\UserViewCounterInterface;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;

interface ViewerRepositoryInterface
{
    public function countVisitors(UserViewCounterInterface $subject, Organization $organization): int;

    public function findOneByDateAndUser(UserViewCounterInterface $subject, \DateTimeInterface $date, ?User $user = null): ?AbstractUserView;
}
