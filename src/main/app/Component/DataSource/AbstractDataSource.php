<?php

namespace Claroline\AppBundle\Component\DataSource;

use Claroline\AppBundle\Component\Context\ContextSubjectInterface;

abstract class AbstractDataSource implements DataSourceInterface
{
    public function supportsSubject(ContextSubjectInterface $subject): bool
    {
        return true;
    }
}
