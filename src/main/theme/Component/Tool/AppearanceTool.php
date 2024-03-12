<?php

namespace Claroline\ThemeBundle\Component\Tool;

use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\CoreBundle\Component\Context\AccountContext;
use Claroline\ThemeBundle\Manager\ThemeManager;

class AppearanceTool extends AbstractTool
{
    public function __construct(
        private readonly ThemeManager $themeManager
    ) {
    }

    public static function getName(): string
    {
        return 'appearance';
    }

    public static function getIcon(): string
    {
        return 'paintbrush';
    }

    public function isRequired(string $context, ContextSubjectInterface $contextSubject = null): bool
    {
        return true;
    }

    public function supportsContext(string $context): bool
    {
        return in_array($context, [
            AccountContext::getName(),
        ]);
    }

    public function open(string $context, ContextSubjectInterface $contextSubject = null): ?array
    {
        return [
            'availableThemes' => $this->themeManager->getAvailableThemes(),
            'theme' => $this->themeManager->getAppearance(),
        ];
    }
}
