<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\DataFixtures\PostInstall;

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
    private $om;
    private $translator;
    private $config;
    private $templateTypeRepo;
    private $templateRepo;
    private $availableLocales;

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
        $this->om = $om;
        $this->templateTypeRepo = $om->getRepository(TemplateType::class);
        $this->templateRepo = $om->getRepository(Template::class);
        $this->availableLocales = $this->config->getParameter('locales.available');

        // Course templates
        $this->createTemplate('training_course', function ($locale) {
            return "
                %course_poster%
                <h1>%course_name% <small>%course_code%</small></h1>
                
                <h2>{$this->translator->trans('description', [], 'platform')}</h2>
                <p>%course_description%</p>
                <h2>{$this->translator->trans('information', [], 'platform')}</h2>
                <ul>
                    <li><b>{$this->translator->trans('public_registration', [], 'platform')} : </b> %course_public_registration%</li>
                    <li><b>{$this->translator->trans('duration', [], 'platform')} : </b> %course_default_duration%</li>
                    <li><b>{$this->translator->trans('max_participants', [], 'cursus')} : </b> %course_max_users%</li>
                </ul>
            ";
        });

        // Session templates
        $this->createTemplate('training_session', function ($locale) {
            return "
                %session_poster%
                <h1>%session_name% <small>%session_code%</small></h1>
                
                <h2>{$this->translator->trans('description', [], 'platform')}</h2>
                <p>%session_description%</p>
                <h2>{$this->translator->trans('information', [], 'platform')}</h2>
                <ul>
                    <li><b>{$this->translator->trans('access_dates', [], 'platform')} : </b> {$this->translator->trans('date_range', ['start' => '%session_start%', 'end' => '%session_end%'], 'platform')}</li>
                    <li><b>{$this->translator->trans('public_registration', [], 'platform')} : </b> %session_public_registration%</li>
                    <li><b>{$this->translator->trans('duration', [], 'platform')} : </b> %session_default_duration%</li>
                    <li><b>{$this->translator->trans('max_participants', [], 'cursus')} : </b> %session_max_users%</li>
                </ul>
            ";
        });

        $this->createTemplate('training_session_invitation', function ($locale) {
            return '
                %session_name%<br/>
                [%session_start% -> %session_end%]<br/>
                %session_description%
            ';
        });

        $this->createTemplate('training_session_confirmation', function ($locale) {
            return '
                %session_name%<br/>
                [%session_start% -> %session_end%]<br/>
                %session_description%<br/><br/>
                <a href="%registration_confirmation_url%">'.$this->translator->trans('confirm_registration', [], 'actions').'</a>
            ';
        });

        // Quota templates
        $this->createTemplate('training_quota', function ($locale) {
            return '
                %organization_name%<br/>
                %quota_threshold%<br/>
                %subscriptions_count%<br/>
                %subscriptions%<br/>
            ';
        });

        $this->createTemplate('training_quota_set_status', function ($locale) {
            return '
                %session_name%<br/>
                %user_first_name%<br/>
                %user_last_name%<br/>
                %session_start%<br/>
                %session_end%<br/>
                %status%<br/>
            ';
        });

        // Event templates
        $this->createTemplate('training_event', function ($locale) {
            return '
                %event_name%<br/>
                [%event_start% -> %event_end%]<br/>
                %event_description%<br/><br/>
                %event_location_address%<br/>
            ';
        });

        $this->createTemplate('training_event_invitation', function ($locale) {
            return '
                %event_name%<br/>
                [%event_start% -> %event_end%]<br/>
                %event_description%<br/><br/>
                %event_location_address%<br/>
            ';
        });

        $this->createTemplate('training_event_presences', function ($locale) {
            return '
                %event_name%<br/>
                [%event_start% -> %event_end%]<br/>
                %event_description%<br/><br/>
                %event_presences_table%<br/>
            ';
        });

        $this->createTemplate('training_event_presence', function ($locale) {
            return '
                %event_name%<br/>
                [%event_start% -> %event_end%]<br/>
                %event_description%<br/><br/>
                <ul>
                <li><b>'.$this->translator->trans('user', [], 'platform', $locale).'</b> : %user_first_name% %user_last_name%</li>
                <li><b>'.$this->translator->trans('status', [], 'platform', $locale).'</b> : %event_presence_status%</li>
                </ul>
            ';
        });

        $om->flush();
    }

    private function createTemplate(string $name, callable $getContent): void
    {
        /** @var TemplateType $templateType */
        $templateType = $this->templateTypeRepo->findOneBy(['name' => $name]);
        $templates = $this->templateRepo->findBy(['name' => $name]);

        if ($templateType && empty($templates)) {
            $template = new Template();
            $template->setType($templateType);
            $template->setName($name);
            $this->om->persist($template);

            foreach ($this->availableLocales as $locale) {
                $templateContent = new TemplateContent();
                $template->addTemplateContent($templateContent);

                $templateContent->setLang($locale);
                $templateContent->setTitle($this->translator->trans($name, [], 'template', $locale));
                $templateContent->setContent($getContent($locale));

                $this->om->persist($templateContent);
            }

            $templateType->setDefaultTemplate($name);
            $this->om->persist($templateType);
        }
    }
}
