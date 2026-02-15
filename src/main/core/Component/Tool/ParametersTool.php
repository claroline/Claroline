<?php

namespace Claroline\CoreBundle\Component\Tool;

use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\ToolComponent;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Component\Context\AdministrationContext;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Manager\LocaleManager;

class ParametersTool extends ToolComponent
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

    public function isRequired(string $context, ?ContextSubjectInterface $contextSubject = null): bool
    {
        return true;
    }

    public function supportsContext(string $context): bool
    {
        return AdministrationContext::getName() === $context;
    }

    public function open(OrderedTool $tool, string $context, ?ContextSubjectInterface $contextSubject = null): ?array
    {
        $parameters = $this->serializer->serialize();

        return [
            'lockedParameters' => $parameters['lockedParameters'] ?? [],
            'parameters' => $parameters,
            'availableLocales' => $this->localeManager->getAvailableLocales(),
        ];
    }
}
