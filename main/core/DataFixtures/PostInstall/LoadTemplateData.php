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
use Claroline\CoreBundle\Entity\Template\TemplateType;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;

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
            $mailRegistrationFR = new Template();
            $mailRegistrationFR->setName('claro_mail_registration');
            $mailRegistrationFR->setType($mailRegistrationType);
            $mailRegistrationFR->setTitle('Inscription à %platform_name%');
            $content = "<div>Votre nom d'utilisateur est %username%</div></br>";
            $content .= '<div>Votre mot de passe est %password%</div>';
            $content .= '<div>%validation_mail%</div>';
            $mailRegistrationFR->setContent($content);
            $mailRegistrationFR->setLang('fr');
            $om->persist($mailRegistrationFR);

            $mailRegistrationEN = new Template();
            $mailRegistrationEN->setName('claro_mail_registration');
            $mailRegistrationEN->setType($mailRegistrationType);
            $mailRegistrationEN->setTitle('Registration to %platform_name%');
            $content = '<div>You username is %username%</div></br>';
            $content .= '<div>Your password is %password%</div>';
            $content .= '<div>%validation_mail%</div>';
            $mailRegistrationEN->setContent($content);
            $mailRegistrationEN->setLang('en');
            $om->persist($mailRegistrationEN);

            $mailRegistrationType->setDefaultTemplate('claro_mail_registration');
            $om->persist($mailRegistrationType);
        }

        if ($mailLayoutType) {
            $mailLayoutFR = new Template();
            $mailLayoutFR->setName('claro_mail_layout');
            $mailLayoutFR->setType($mailLayoutType);
            $mailLayoutFR->setContent('<div></div>%content%<div></hr>Powered by %platform_name%</div>');
            $mailLayoutFR->setLang('fr');
            $om->persist($mailLayoutFR);

            $mailLayoutEN = new Template();
            $mailLayoutEN->setName('claro_mail_layout');
            $mailLayoutEN->setType($mailLayoutType);
            $mailLayoutEN->setContent('<div></div>%content%<div></hr>Powered by %platform_name%</div>');
            $mailLayoutEN->setLang('en');
            $om->persist($mailLayoutEN);

            $mailLayoutType->setDefaultTemplate('claro_mail_layout');
            $om->persist($mailLayoutType);
        }

        if ($forgottenPasswordType) {
            foreach ($this->config->getParameter('locales.available') as $locale) {
                $template = new Template();
                $template->setType($forgottenPasswordType);
                $template->setName('forgotten_password');
                $template->setLang($locale);

                $title = $this->translator->trans('resetting_your_password', [], 'platform', $locale);
                $template->setTitle($title);

                $content = '<div>'.$this->translator->trans('reset_password_txt', [], 'platform', $locale).'</div>';
                $content .= '<div>'.$this->translator->trans('your_username', [], 'platform', $locale).' : %username%</div>';
                $content .= '<a href="%password_reset_link%">'.$this->translator->trans('mail_click', [], 'platform', $locale).'</a>';
                $template->setContent($content);
                $om->persist($template);
            }
            $forgottenPasswordType->setDefaultTemplate('forgotten_password');
            $om->persist($forgottenPasswordType);
        }

        if ($passwordInitializationType) {
            foreach ($this->config->getParameter('locales.available') as $locale) {
                $template = new Template();
                $template->setType($passwordInitializationType);
                $template->setName('password_initialization');
                $template->setLang($locale);

                $title = $this->translator->trans('initialize_your_password', [], 'platform', $locale);
                $template->setTitle($title);

                $content = '<div>'.$this->translator->trans('initialize_your_password', [], 'platform', $locale).'</div>';
                $content .= '<div>'.$this->translator->trans('your_username', [], 'platform', $locale).' : %username%</div>';
                $content .= '<a href="%password_initialization_link%">'.$this->translator->trans('mail_click', [], 'platform', $locale).'</a>';
                $template->setContent($content);
                $om->persist($template);
            }
            $passwordInitializationType->setDefaultTemplate('password_initialization');
            $om->persist($passwordInitializationType);
        }

        if ($userActivationType) {
            foreach ($this->config->getParameter('locales.available') as $locale) {
                $template = new Template();
                $template->setType($userActivationType);
                $template->setName('user_activation');
                $template->setLang($locale);

                $title = $this->translator->trans('activate_account', [], 'platform', $locale);
                $template->setTitle($title);

                $content = '<div>'.$this->translator->trans('activate_account', [], 'platform', $locale).'</div>';
                $content .= '<a href="%user_activation_link%">'.$this->translator->trans('activate_account_click', [], 'platform', $locale).'</a>';
                $template->setContent($content);
                $om->persist($template);
            }
            $userActivationType->setDefaultTemplate('user_activation');
            $om->persist($userActivationType);
        }

        if ($mailValidationType) {
            foreach ($this->config->getParameter('locales.available') as $locale) {
                $template = new Template();
                $template->setType($mailValidationType);
                $template->setName('claro_mail_validation');
                $template->setLang($locale);

                $title = $this->translator->trans('email_validation', [], 'platform', $locale);
                $template->setTitle($title);

                $content = $this->translator->trans(
                    'email_validation_url_display',
                    ['%url%' => '%validation_mail%'],
                    'platform',
                    $locale
                );
                $template->setContent($content);
                $om->persist($template);
            }
            $mailValidationType->setDefaultTemplate('claro_mail_validation');
            $om->persist($mailValidationType);
        }

        if ($workspaceRegistrationType) {
            foreach ($this->config->getParameter('locales.available') as $locale) {
                $template = new Template();
                $template->setType($workspaceRegistrationType);
                $template->setName('workspace_registration');
                $template->setLang($locale);

                $title = $this->translator->trans(
                    'workspace_registration_message_object',
                    ['%workspace_name%' => '%workspace_name%'],
                    'platform',
                    $locale
                );
                $template->setTitle($title);

                $content = $this->translator->trans(
                    'workspace_registration_message',
                    ['%workspace_name%' => '%workspace_name%'],
                    'platform',
                    $locale
                );
                $template->setContent($content);
                $om->persist($template);
            }
            $workspaceRegistrationType->setDefaultTemplate('workspace_registration');
            $om->persist($workspaceRegistrationType);
        }

        if ($platformRoleRegistrationType) {
            foreach ($this->config->getParameter('locales.available') as $locale) {
                $template = new Template();
                $template->setType($platformRoleRegistrationType);
                $template->setName('platform_role_registration');
                $template->setLang($locale);

                $title = $this->translator->trans('new_role_message_object', [], 'platform', $locale);
                $template->setTitle($title);

                $content = $this->translator->trans('new_role_message', ['%name%' => '%role_name%'], 'platform', $locale);
                $template->setContent($content);
                $om->persist($template);
            }
            $platformRoleRegistrationType->setDefaultTemplate('platform_role_registration');
            $om->persist($platformRoleRegistrationType);
        }

        $om->flush();
    }
}
