<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\DataFixtures\PostInstall;

use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\CoreBundle\Entity\Template\TemplateContent;
use Claroline\CoreBundle\Entity\Template\TemplateType;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadTemplateData extends AbstractFixture implements ContainerAwareInterface
{
    private $translator;
    private $config;

    public function setContainer(ContainerInterface $container = null)
    {
        if (!$container) {
            throw new \LogicException('Expected a service container, got null.');
        }

        $this->translator = $container->get('translator');
        $this->config = $container->get(PlatformConfigurationHandler::class);
    }

    public function load(ObjectManager $om)
    {
        $templateTypeRepo = $om->getRepository(TemplateType::class);
        $templateRepo = $om->getRepository(Template::class);
        $availableLocales = $this->config->getParameter('locales.available');

        $invitationType = $templateTypeRepo->findOneBy(['name' => 'event_invitation']);
        $templates = $templateRepo->findBy(['name' => 'event_invitation']);
        if ($invitationType && empty($templates)) {
            $template = new Template();
            $template->setType($invitationType);
            $template->setName('event_invitation');
            $om->persist($template);

            foreach ($availableLocales as $locale) {
                $templateContent = new TemplateContent();
                $templateContent->setLang($locale);
                $templateContent->setTitle($this->translator->trans('event_invitation', [], 'template', $locale));

                $content = '%event_name%<br/>';
                $content .= '[%event_start% -> %event_end%]<br/>';
                $content .= '<p>%event_description%</p><br/><br/>';
                $content .= '<a class="btn btn-block btn-primary" href="%event_join_url%">'.$this->translator->trans('accept_invitation', [], 'agenda', $locale).'</a><br/>';
                $content .= '<a class="btn btn-block btn-default" href="%event_maybe_url%">'.$this->translator->trans('accept_maybe_invitation', [], 'agenda', $locale).'</a><br/>';
                $content .= '<a class="btn btn-block btn-default" href="%event_decline_url%">'.$this->translator->trans('decline_invitation', [], 'agenda', $locale).'</a><br/>';

                $templateContent->setContent($content);
                $om->persist($templateContent);
            }

            $invitationType->setDefaultTemplate('event_invitation');
            $om->persist($invitationType);
        }

        $om->flush();
    }
}
