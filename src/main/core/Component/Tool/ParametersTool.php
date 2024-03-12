<?php

namespace Claroline\CoreBundle\Component\Tool;

use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Component\Context\AccountContext;
use Claroline\CoreBundle\Component\Context\AdministrationContext;
use Claroline\CoreBundle\Manager\LocaleManager;

class ParametersTool extends AbstractTool
{
    public function __construct(
        private readonly ParametersSerializer $serializer,
        private readonly LocaleManager $localeManager
    ) {
    }

    public static function getName(): string
    {
        return 'parameters';
    }

    public static function getIcon(): string
    {
        return 'cog';
    }

    public function isRequired(string $context, ContextSubjectInterface $contextSubject = null): bool
    {
        return true;
    }

    public function supportsContext(string $context): bool
    {
        return in_array($context, [
            AccountContext::getName(),
            AdministrationContext::getName(),
        ]);
    }

    public function open(string $context, ContextSubjectInterface $contextSubject = null): ?array
    {
        if (AdministrationContext::getName() === $context) {
            $parameters = $this->serializer->serialize();

            return [
                'lockedParameters' => $parameters['lockedParameters'] ?? [],
                'parameters' => $parameters,
                'availableLocales' => $this->localeManager->getAvailableLocales(),
            ];
        }

        return [];
    }

    public function configure(string $context, ContextSubjectInterface $contextSubject = null, array $configData = []): ?array
    {
        return [];
    }
}
