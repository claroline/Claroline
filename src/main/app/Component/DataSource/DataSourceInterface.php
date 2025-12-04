<?php

namespace Claroline\AppBundle\Component\DataSource;

use Claroline\AppBundle\Component\ComponentInterface;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Context\ContextualInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface DataSourceInterface extends ComponentInterface, ContextualInterface
{
    public function open(string $context, ?ContextSubjectInterface $contextSubject = null, ?bool $filterSubject = true, ?Request $request = null): Response;

    public static function getType(): string;
}
