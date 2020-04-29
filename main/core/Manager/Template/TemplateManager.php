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
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;

class TemplateManager
{
    /** @var ObjectManager */
    private $om;
    /** @var PlatformConfigurationHandler */
    private $config;

    private $templateTypeRepo;
    private $templateRepo;

    /**
     * TemplateManager constructor.
     *
     * @param ObjectManager                $om
     * @param PlatformConfigurationHandler $config
     */
    public function __construct(
        ObjectManager $om,
        PlatformConfigurationHandler $config
    ) {
        $this->om = $om;
        $this->config = $config;

        $this->templateTypeRepo = $om->getRepository(TemplateType::class);
        $this->templateRepo = $om->getRepository(Template::class);
    }

    /**
     * @param Template $template
     */
    public function defineTemplateAsDefault(Template $template)
    {
        $templateType = $template->getType();
        $templateType->setDefaultTemplate($template->getName());
        $this->om->persist($templateType);
        $this->om->flush();
    }

    /**
     * @param string      $templateTypeName
     * @param array       $placeholders
     * @param string|null $locale
     * @param string      $mode
     *
     * @return string
     */
    public function getTemplate($templateTypeName, $placeholders = [], $locale = null, $mode = 'content')
    {
        $result = '';
        $templateType = $this->templateTypeRepo->findOneBy(['name' => $templateTypeName]);

        // Checks if a template is associated to the template type
        if ($templateType && $templateType->getDefaultTemplate()) {
            /** @var Template|null $template */
            $template = null;

            // Fetches template for the given type and locale
            if ($locale) {
                $template = $this->templateRepo->findOneBy([
                    'type' => $templateType,
                    'name' => $templateType->getDefaultTemplate(),
                    'lang' => $locale,
                ]);
            }

            // If no template is found for the given locale or locale is null, uses default locale
            if (!$locale || !$template) {
                $defaultLocale = $this->config->getParameter('locales.default');
                if ($defaultLocale && $defaultLocale !== $locale) {
                    $template = $this->templateRepo->findOneBy([
                        'type' => $templateType,
                        'name' => $templateType->getDefaultTemplate(),
                        'lang' => $defaultLocale,
                    ]);
                }
            }

            // If a template is found
            if ($template) {
                $result = $this->getTemplateContent($template, $placeholders, $mode);
            }
        }

        return $result;
    }

    /**
     * @param Template $template
     * @param array    $placeholders
     * @param string   $mode
     *
     * @return string
     */
    public function getTemplateContent(Template $template, $placeholders = [], $mode = 'content')
    {
        switch ($mode) {
            case 'content':
                return $this->replacePlaceholders($template->getContent(), $placeholders);
            case 'title':
                return $this->replacePlaceholders($template->getTitle() ?? '', $placeholders);
        }

        return '';
    }

    /**
     * @param string $text
     * @param array  $placeholders
     *
     * @return string
     */
    public function replacePlaceholders($text, $placeholders = [])
    {
        $now = new \DateTime();
        $keys = [
            '%platform_name%',
            '%platform_url%',
            '%date%',
            '%datetime%',
        ];
        $values = [
            $this->config->getParameter('display.name'),
            $this->config->getParameter('internet.platform_url'),
            $now->format('Y-m-d'), // should be in locale format
            $now->format('Y-m-d H:i:s'), // should be in locale format
        ];

        foreach ($placeholders as $key => $value) {
            $keys[] = '%'.$key.'%';
            $values[] = $value;
        }

        return str_replace($keys, $values, $text);
    }
}
