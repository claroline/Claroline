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
use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\CoreBundle\Entity\Template\TemplateType;
use Claroline\CoreBundle\Manager\LocaleManager;
use Doctrine\Persistence\ObjectRepository;

class TemplateManager
{
    private ObjectManager $om;
    private LocaleManager $localeManager;
    private PlaceholderManager $placeholderManager;

    private ObjectRepository $templateTypeRepo;
    private ObjectRepository $templateRepo;

    public function __construct(
        ObjectManager $om,
        LocaleManager $localeManager,
        PlaceholderManager $placeholderManager
    ) {
        $this->om = $om;
        $this->localeManager = $localeManager;
        $this->placeholderManager = $placeholderManager;

        $this->templateTypeRepo = $om->getRepository(TemplateType::class);
        $this->templateRepo = $om->getRepository(Template::class);
    }

    public function defineTemplateAsDefault(Template $template): void
    {
        $templateType = $template->getType();
        $templateType->setDefaultTemplate($template->getName());

        $this->om->persist($templateType);
        $this->om->flush();
    }

    public function getTemplate(string $templateTypeName, array $placeholders = [], string $locale = null, string $mode = 'content'): string
    {
        $result = '';
        $templateType = $this->templateTypeRepo->findOneBy(['name' => $templateTypeName]);

        if (!$locale) {
            $locale = $this->localeManager->getDefault();
        }

        // Checks if a template is associated to the template type
        if ($templateType && $templateType->getDefaultTemplate()) {
            /** @var Template $template */
            $template = $this->templateRepo->findOneBy([
                'type' => $templateType,
                'name' => $templateType->getDefaultTemplate(),
            ]);

            if ($template) {
                $result = $this->getTemplateContent($template, $placeholders, $locale, $mode);
            }
        }

        return $result;
    }

    public function getTemplateContent(Template $template, array $placeholders = [], string $locale = null, string $mode = 'content'): string
    {
        $content = null;
        if ($locale) {
            $content = $template->getTemplateContent($locale);
        }

        // content for the requested locale does not exist. Try with platform default locale
        $defaultLocale = $this->localeManager->getDefault();
        if (empty($content) && $locale !== $defaultLocale) {
            $content = $template->getTemplateContent($defaultLocale);
        }

        if ($content) {
            switch ($mode) {
                case 'content':
                    return $this->placeholderManager->replacePlaceholders($content->getContent() ?? '', $placeholders);
                case 'title':
                    return $this->placeholderManager->replacePlaceholders($content->getTitle() ?? '', $placeholders);
            }
        }

        return '';
    }

    public function formatDatePlaceholder(string $placeholderPrefix, ?\DateTime $date): array
    {
        return $this->placeholderManager->formatDatePlaceholder($placeholderPrefix, $date);
    }
}
