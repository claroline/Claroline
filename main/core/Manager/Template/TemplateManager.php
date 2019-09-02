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

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\CoreBundle\Entity\Template\TemplateType;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.template_manager")
 */
class TemplateManager
{
    /** @var ObjectManager */
    private $om;

    private $parameters;

    private $templateTypeRepo;
    private $templateRepo;

    /**
     * @DI\InjectParams({
     *     "om"                   = @DI\Inject("claroline.persistence.object_manager"),
     *     "parametersSerializer" = @DI\Inject("claroline.serializer.parameters")
     * })
     *
     * @param ObjectManager        $om
     * @param ParametersSerializer $parametersSerializer
     */
    public function __construct(ObjectManager $om, ParametersSerializer $parametersSerializer)
    {
        $this->om = $om;
        $this->serializer = $parametersSerializer;

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
        $parameters = $this->serializer->serialize([Options::SERIALIZE_MINIMAL]);
        $result = '';
        $templateType = $this->templateTypeRepo->findOneBy(['name' => $templateTypeName]);

        // Checks if a template is associated to the template type
        if ($templateType && $templateType->getDefaultTemplate()) {
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
                $defaultLocale = isset($parameters['locales']['default']) ? $parameters['locales']['default'] : null;

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
                switch ($mode) {
                    case 'content':
                        $result = $this->replacePlaceholders($template->getContent(), $placeholders);
                        break;
                    case 'title':
                        $result = $template->getTitle() ?
                            $this->replacePlaceholders($template->getTitle(), $placeholders) :
                            '';
                        break;
                }
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
                return $template->getTitle() ?
                    $this->replacePlaceholders($template->getTitle(), $placeholders) :
                    '';
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
        $parameters = $this->serializer->serialize([Options::SERIALIZE_MINIMAL]);
        $now = new \DateTime();
        $keys = [
            '%platform_name%',
            '%platform_url%',
            '%date%',
            '%datetime%',
        ];
        $values = [
            $parameters['display']['name'],
            $parameters['internet']['platform_url'],
            $now->format('Y-m-d'),
            $now->format('Y-m-d H:i:s'),
        ];

        foreach ($placeholders as $key => $value) {
            $keys[] = '%'.$key.'%';
            $values[] = $value;
        }

        return str_replace($keys, $values, $text);
    }
}
