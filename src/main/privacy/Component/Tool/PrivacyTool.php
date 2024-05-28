<?php

namespace Claroline\PrivacyBundle\Component\Tool;

use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Component\Context\AccountContext;
use Claroline\CoreBundle\Component\Context\AdministrationContext;

class PrivacyTool extends AbstractTool
{
    public function __construct(
        private readonly ParametersSerializer $serializer
    ) {
    }

    public static function getName(): string
    {
        return 'privacy';
    }

    public static function getIcon(): string
    {
        return 'user-shield';
    }

    public function supportsContext(string $context): bool
    {
        return in_array($context, [
            AccountContext::getName(),
            AdministrationContext::getName(),
        ]);
    }

    public function isRequired(string $context, ContextSubjectInterface $contextSubject = null): bool
    {
        return true;
    }

    public function open(string $context, ContextSubjectInterface $contextSubject = null): ?array
    {
        if (AdministrationContext::getName() === $context) {
            $parameters = $this->serializer->serialize();

            return [
                'lockedParameters' => $parameters['lockedParameters'] ?? [],
                'parameters' => $parameters,
            ];
        }

        return [];
    }
}
