<?php

namespace Claroline\AppBundle\Component\DataSource;

use Claroline\AppBundle\Component\ComponentInterface;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Context\ContextualInterface;

interface DataSourceInterface extends ComponentInterface, ContextualInterface
{
    // public static function getIcon(): string;

    public function open(string $context, ContextSubjectInterface $contextSubject = null): ?array;
}
