<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager\Template;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\TemplateBundle\Component\Template\TemplateProvider;
use Claroline\TemplateBundle\Entity\Template;
use Claroline\TemplateBundle\Library\CompiledTemplate;
use Claroline\TemplateBundle\Model\TemplateInterface;

/**
 * @todo move in TemplateBundle.
 */
class TemplateManager
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly LocaleManager $localeManager,
        private readonly PlaceholderManager $placeholderManager,
        private readonly TemplateProvider $templateProvider,
    ) {
    }

    /**
     * @param string|Template $template - The name of a Template component or a Template entity. If the name of a component is provided, we will retrieve the default template for the type
     */
    public function compile(string|TemplateInterface $template, ?array $values = [], ?string $locale = null): CompiledTemplate
    {
        if (is_string($template)) {
            $template = $this->getDefaultTemplate($template);
        }

        $content = null;
        if ($locale) {
            $content = $template->getTemplateContent($locale);
        }

        // The template for the requested locale does not exist. Try with platform default locale
        $defaultLocale = $this->localeManager->getDefault();
        if (empty($content) && $locale !== $defaultLocale) {
            $content = $template->getTemplateContent($defaultLocale);
            $locale = $defaultLocale;
        }

        return new CompiledTemplate(
            $locale,
            $this->placeholderManager->replacePlaceholders($content?->getTitle() ?? '', $values),
            $this->placeholderManager->replacePlaceholders($content?->getContent() ?? '', $values)
        );
    }

    /**
     * @deprecated use TemplateManager::compile()
     */
    public function getTemplate(string $templateType, array $placeholders = [], ?string $locale = null, string $mode = 'content'): string
    {
        $compiledTemplate = $this->compile($templateType, $placeholders, $locale);

        if ('content' === $mode) {
            return $compiledTemplate->getContent();
        }

        return $compiledTemplate->getTitle();
    }

    /**
     * @deprecated use TemplateManager::compile()
     */
    public function getTemplateContent(Template $template, array $placeholders = [], ?string $locale = null, string $mode = 'content'): string
    {
        $compiledTemplate = $this->compile($template, $placeholders, $locale);

        if ('content' === $mode) {
            return $compiledTemplate->getContent();
        }

        return $compiledTemplate->getTitle();
    }

    public function formatDatePlaceholder(string $placeholderPrefix, ?\DateTimeInterface $date): array
    {
        return $this->placeholderManager->formatDatePlaceholder($placeholderPrefix, $date);
    }

    /**
     * Get the default Template for a type. If none is defined, it fallbacks on the system template defined by the component.
     */
    private function getDefaultTemplate(string $templateType): TemplateInterface
    {
        $defaultTemplate = $this->om->getRepository(Template::class)->findOneBy([
            'type' => $templateType,
            'default' => true,
        ]);

        if ($defaultTemplate) {
            return $defaultTemplate;
        }

        $component = $this->templateProvider->getTemplate($templateType);

        return $component->getSystemTemplate();
    }
}
