<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Installation\Updater;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\CoreBundle\Entity\Template\TemplateType;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class Updater120536 extends Updater
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

        $this->om = $container->get('Claroline\AppBundle\Persistence\ObjectManager');
        $this->parameters = $container->get('Claroline\CoreBundle\API\Serializer\ParametersSerializer')->serialize([Options::SERIALIZE_MINIMAL]);
        $this->translator = $container->get('translator');

        $this->templateRepo = $this->om->getRepository(Template::class);
        $this->templateTypeRepo = $this->om->getRepository(TemplateType::class);
    }

    public function postUpdate()
    {
        $this->generateDefaultNewMessageTemplate();
    }

    private function generateDefaultNewMessageTemplate()
    {
        $templateType = $this->templateTypeRepo->findOneBy(['name' => 'forum_new_message']);
        $templates = $this->templateRepo->findBy(['type' => $templateType]);

        if (0 === count($templates)) {
            $this->log('Generating default template for forum new messages...');

            $this->om->startFlushSuite();

            foreach ($this->parameters['locales']['available'] as $locale) {
                $template = new Template();
                $template->setType($templateType);
                $template->setName('forum_new_message');
                $template->setLang($locale);

                $template->setTitle('%subject%');

                $template->setContent(
                    '<p>%message%</p>'.
                    '<p>'.$this->translator->trans('posted_by', ['author' => 'author', 'date' => 'date'], 'forum', $locale).'</p>'.
                    '<br/>'.
                    '<a href="%subject_url%">'.$this->translator->trans('show-subject', [], 'actions').'</a>'.
                    '<br/>'.
                    '<a href="%forum_url%">'.$this->translator->trans('show-forum', [], 'actions').'</a>'.
                    '<br/>'.
                    '<br/>'.
                    '<a href="%workspace_url%">'.$this->translator->trans('show-workspace', [], 'actions').'</a>'
                );
                $this->om->persist($template);
            }
            $templateType->setDefaultTemplate('forum_new_message');
            $this->om->persist($templateType);

            $this->om->endFlushSuite();

            $this->log('Default template for forum new messages has been generated.');
        }
    }
}
