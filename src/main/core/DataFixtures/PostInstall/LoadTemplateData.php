<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\PostInstall;

use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\CoreBundle\Entity\Template\TemplateContent;
use Claroline\CoreBundle\Entity\Template\TemplateType;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoadTemplateData extends AbstractFixture implements ContainerAwareInterface
{
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var TranslatorInterface */
    private $translator;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->translator = $container->get('translator');
        $this->config = $container->get(PlatformConfigurationHandler::class);
    }

    public function load(ObjectManager $om)
    {
        $templateTypeRepo = $om->getRepository(TemplateType::class);

        /** @var TemplateType $mailRegistrationType */
        $mailRegistrationType = $templateTypeRepo->findOneBy(['name' => 'claro_mail_registration']);
        /** @var TemplateType $mailLayoutType */
        $mailLayoutType = $templateTypeRepo->findOneBy(['name' => 'claro_mail_layout']);
        /** @var TemplateType $forgottenPasswordType */
        $forgottenPasswordType = $templateTypeRepo->findOneBy(['name' => 'forgotten_password']);
        /** @var TemplateType $passwordInitializationType */
        $passwordInitializationType = $templateTypeRepo->findOneBy(['name' => 'password_initialization']);
        /** @var TemplateType $userActivationType */
        $userActivationType = $templateTypeRepo->findOneBy(['name' => 'user_activation']);
        /** @var TemplateType $mailValidationType */
        $mailValidationType = $templateTypeRepo->findOneBy(['name' => 'claro_mail_validation']);
        /** @var TemplateType $workspaceRegistrationType */
        $workspaceRegistrationType = $templateTypeRepo->findOneBy(['name' => 'workspace_registration']);
        /** @var TemplateType $platformRoleRegistrationType */
        $platformRoleRegistrationType = $templateTypeRepo->findOneBy(['name' => 'platform_role_registration']);

        if ($mailRegistrationType) {
            $mailRegistration = new Template();
            $mailRegistration->setName('claro_mail_registration');
            $mailRegistration->setType($mailRegistrationType);
            $om->persist($mailRegistration);

            $mailRegistrationFR = new TemplateContent();
            $mailRegistration->addTemplateContent($mailRegistrationFR);

            $mailRegistrationFR->setTitle('Inscription à %platform_name%');
            $content = "<div>Votre nom d'utilisateur est %username%</div></br>";
            $content .= '<div>Votre mot de passe est %password%</div>';
            $content .= '<div>%validation_mail%</div>';
            $mailRegistrationFR->setContent($content);
            $mailRegistrationFR->setLang('fr');
            $om->persist($mailRegistrationFR);

            $mailRegistrationEN = new TemplateContent();
            $mailRegistration->addTemplateContent($mailRegistrationEN);

            $mailRegistrationEN->setTitle('Registration to %platform_name%');
            $content = '<div>Your username is %username%</div></br>';
            $content .= '<div>Your password is %password%</div>';
            $content .= '<div>%validation_mail%</div>';
            $mailRegistrationEN->setContent($content);
            $mailRegistrationEN->setLang('en');
            $om->persist($mailRegistrationEN);

            $mailRegistrationType->setDefaultTemplate('claro_mail_registration');
            $om->persist($mailRegistrationType);
        }

        if ($mailLayoutType) {
            $mailLayout = new Template();
            $mailLayout->setName('claro_mail_layout');
            $mailLayout->setType($mailLayoutType);
            $om->persist($mailLayout);

            $mailLayoutFR = new TemplateContent();
            $mailLayout->addTemplateContent($mailLayoutFR);

            $mailLayoutFR->setContent('<div></div>%content%<div></hr>Powered by %platform_name%</div>');
            $mailLayoutFR->setLang('fr');
            $om->persist($mailLayoutFR);

            $mailLayoutEN = new TemplateContent();
            $mailLayout->addTemplateContent($mailLayoutEN);

            $mailLayoutEN->setContent('<div></div>%content%<div></hr>Powered by %platform_name%</div>');
            $mailLayoutEN->setLang('en');
            $om->persist($mailLayoutEN);

            $mailLayoutType->setDefaultTemplate('claro_mail_layout');
            $om->persist($mailLayoutType);
        }

        if ($forgottenPasswordType) {
            $template = new Template();
            $template->setType($forgottenPasswordType);
            $template->setName('forgotten_password');
            $om->persist($template);

            foreach ($this->config->getParameter('locales.available') as $locale) {
                $templateContent = new TemplateContent();
                $template->addTemplateContent($templateContent);
                $templateContent->setLang($locale);

                $title = $this->translator->trans('resetting_your_password', [], 'platform', $locale);
                $templateContent->setTitle($title);

                $content = '<div>'.$this->translator->trans('reset_password_txt', [], 'platform', $locale).'</div>';
                $content .= '<div>'.$this->translator->trans('your_username', [], 'platform', $locale).' : %username%</div>';
                $content .= '<a href="%password_reset_link%">'.$this->translator->trans('mail_click', [], 'platform', $locale).'</a>';
                $templateContent->setContent($content);
                $om->persist($templateContent);
            }
            $forgottenPasswordType->setDefaultTemplate('forgotten_password');
            $om->persist($forgottenPasswordType);
        }

        if ($passwordInitializationType) {
            $template = new Template();
            $template->setType($passwordInitializationType);
            $template->setName('password_initialization');
            $om->persist($template);

            foreach ($this->config->getParameter('locales.available') as $locale) {
                $templateContent = new TemplateContent();
                $template->addTemplateContent($templateContent);
                $templateContent->setLang($locale);

                $title = $this->translator->trans('initialize_your_password', [], 'platform', $locale);
                $templateContent->setTitle($title);

                $content = '<div>'.$this->translator->trans('initialize_your_password', [], 'platform', $locale).'</div>';
                $content .= '<div>'.$this->translator->trans('your_username', [], 'platform', $locale).' : %username%</div>';
                $content .= '<a href="%password_initialization_link%">'.$this->translator->trans('mail_click', [], 'platform', $locale).'</a>';
                $templateContent->setContent($content);
                $om->persist($templateContent);
            }
            $passwordInitializationType->setDefaultTemplate('password_initialization');
            $om->persist($passwordInitializationType);
        }

        if ($userActivationType) {
            $template = new Template();
            $template->setType($userActivationType);
            $template->setName('user_activation');
            $om->persist($template);

            foreach ($this->config->getParameter('locales.available') as $locale) {
                $templateContent = new TemplateContent();
                $template->addTemplateContent($templateContent);
                $templateContent->setLang($locale);

                $title = $this->translator->trans('activate_account', [], 'platform', $locale);
                $templateContent->setTitle($title);

                $content = '<div>'.$this->translator->trans('activate_account', [], 'platform', $locale).'</div>';
                $content .= '<a href="%user_activation_link%">'.$this->translator->trans('activate_account_click', [], 'platform', $locale).'</a>';
                $templateContent->setContent($content);
                $om->persist($templateContent);
            }
            $userActivationType->setDefaultTemplate('user_activation');
            $om->persist($userActivationType);
        }

        if ($mailValidationType) {
            $template = new Template();
            $template->setType($mailValidationType);
            $template->setName('claro_mail_validation');
            $om->persist($template);

            foreach ($this->config->getParameter('locales.available') as $locale) {
                $templateContent = new TemplateContent();
                $template->addTemplateContent($templateContent);
                $templateContent->setLang($locale);

                $title = $this->translator->trans('email_validation', [], 'platform', $locale);
                $templateContent->setTitle($title);

                $content = $this->translator->trans(
                    'email_validation_url_display',
                    ['%url%' => '%validation_mail%'],
                    'platform',
                    $locale
                );
                $templateContent->setContent($content);
                $om->persist($templateContent);
            }
            $mailValidationType->setDefaultTemplate('claro_mail_validation');
            $om->persist($mailValidationType);
        }

        if ($workspaceRegistrationType) {
            $template = new Template();
            $template->setType($workspaceRegistrationType);
            $template->setName('workspace_registration');
            $om->persist($template);

            foreach ($this->config->getParameter('locales.available') as $locale) {
                $templateContent = new TemplateContent();
                $template->addTemplateContent($templateContent);
                $templateContent->setLang($locale);

                $title = $this->translator->trans(
                    'workspace_registration_message_object',
                    ['%workspace_name%' => '%workspace_name%'],
                    'platform',
                    $locale
                );
                $templateContent->setTitle($title);

                $content = $this->translator->trans(
                    'workspace_registration_message',
                    ['%workspace_name%' => '%workspace_name%'],
                    'platform',
                    $locale
                );
                $templateContent->setContent($content);
                $om->persist($templateContent);
            }
            $workspaceRegistrationType->setDefaultTemplate('workspace_registration');
            $om->persist($workspaceRegistrationType);
        }

        if ($platformRoleRegistrationType) {
            $template = new Template();
            $template->setType($platformRoleRegistrationType);
            $template->setName('platform_role_registration');
            $om->persist($template);

            foreach ($this->config->getParameter('locales.available') as $locale) {
                $templateContent = new TemplateContent();
                $template->addTemplateContent($templateContent);
                $templateContent->setLang($locale);

                $title = $this->translator->trans('new_role_message_object', [], 'platform', $locale);
                $templateContent->setTitle($title);

                $content = $this->translator->trans('new_role_message', ['%name%' => '%role_name%'], 'platform', $locale);
                $templateContent->setContent($content);
                $om->persist($templateContent);
            }
            $platformRoleRegistrationType->setDefaultTemplate('platform_role_registration');
            $om->persist($platformRoleRegistrationType);
        }

        $om->flush();
    }
}
