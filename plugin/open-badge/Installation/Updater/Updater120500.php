<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Installation\Updater;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\CoreBundle\Entity\Template\TemplateType;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class Updater120500 extends Updater
{
    protected $logger;

    /** @var ObjectManager */
    private $om;

    /** @var ParametersSerializer */
    private $parameters;

    /** @var TranslatorInterface */
    private $translator;

    private $templateRepo;
    private $templateTypeRepo;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;

        $this->om = $container->get('claroline.persistence.object_manager');
        $this->parameters = $container->get('Claroline\CoreBundle\API\Serializer\ParametersSerializer')->serialize([Options::SERIALIZE_MINIMAL]);
        $this->translator = $container->get('translator');

        $this->templateRepo = $this->om->getRepository(Template::class);
        $this->templateTypeRepo = $this->om->getRepository(TemplateType::class);
    }

    public function postUpdate()
    {
        $this->generateDefaultBadgeTemplate();
    }

    private function generateDefaultBadgeTemplate()
    {
        $templateType = $this->templateTypeRepo->findOneBy(['name' => 'badge_certificate']);
        $badgeTemplates = $this->templateRepo->findBy(['type' => $templateType]);

        if (0 === count($badgeTemplates)) {
            $this->log('Generating default template for badge...');

            $this->om->startFlushSuite();

            foreach ($this->parameters['locales']['available'] as $locale) {
                $template = new Template();
                $template->setType($templateType);
                $template->setName('badge_certificate');
                $template->setLang($locale);
                $content = '<div style="background-color: #f2ede7; padding: 20px;">'.
                    '<div style="text-align: left;"><strong>%issuer_name%</strong></div>'.
                    '<br /><hr /><br />'.
                    '<h1 style="text-align: center;">%badge_image% %badge_name%</h1>'.
                    '<div style="text-align: center;">%badge_description%</div>'.
                    '<br /><hr /><br />'.
                    '<div style="text-align: center;">'.$this->translator->trans('badge_awarded_to', [], 'template', $locale).'</div>'.
                    '<h2 style="text-align: center;">%first_name% %last_name%</h2>'.
                    '</div>';
                $template->setContent($content);
                $this->om->persist($template);
            }
            $templateType->setDefaultTemplate('badge_certificate');
            $this->om->persist($templateType);

            $this->om->endFlushSuite();

            $this->log('Default template for badge has been generated.');
        }
    }
}
