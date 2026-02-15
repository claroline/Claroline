<?php

namespace Claroline\TemplateBundle\Component\Tool;

use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\ToolComponent;
use Claroline\CoreBundle\Component\Context\AdministrationContext;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\TemplateBundle\Component\Template\TemplateProvider;

class TemplatesTool extends ToolComponent
{
    public function __construct(
        private readonly TemplateProvider $templateProvider
    ) {
    }

    public static function getName(): string
    {
        return 'templates';
    }

    public static function getIcon(): string
    {
        return 'stamp';
    }

    public function supportsContext(string $context): bool
    {
        return AdministrationContext::getName() === $context;
    }

    public function isRequired(string $context, ?ContextSubjectInterface $contextSubject = null): bool
    {
        return true;
    }

    public function open(OrderedTool $tool, string $context, ?ContextSubjectInterface $contextSubject = null): ?array
    {
        return [
            'templateTypes' => $this->templateProvider->getAvailableTemplates(),
        ];
    }
}
