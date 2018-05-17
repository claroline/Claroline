<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Administration;

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Form\Field\ContentType;
use Claroline\CoreBundle\Library\Configuration\PlatformDefaults;
use Claroline\CoreBundle\Validator\Constraints\DomainName;
use Claroline\CoreBundle\Validator\Constraints\FileSize;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GeneralType extends AbstractType
{
    private $langs;
    private $role;
    private $description;
    private $dateFormat;
    private $language;
    private $lockedParams;
    private $targetLoginUrls;

    public function __construct(
        array $langs,
        $role,
        $description,
        $dateFormat,
        $language,
        array $lockedParams = [],
        array $targetLoginUrls = []
    ) {
        $this->role = $role;
        $this->description = $description;
        $this->dateFormat = $dateFormat;
        $this->language = $language;

        if (!empty($langs)) {
            $this->langs = $langs;
        } else {
            $this->langs = ['en' => 'en', 'fr' => 'fr'];
        }
        $this->lockedParams = $lockedParams;
        $this->targetLoginUrls = $targetLoginUrls;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'required' => false,
                    'disabled' => isset($this->lockedParams['name']),
                    'label' => 'name',
                ]
            )
            ->add(
                'description',
                ContentType::class,
                [
                    'data' => $this->description,
                    'mapped' => false,
                    'required' => false,
                    'label' => 'description',
                    'theme_options' => ['contentTitle' => false, 'tinymce' => false],
                ]
            )
            ->add(
                'supportEmail',
                EmailType::class,
                [
                    'label' => 'support_email',
                    'disabled' => isset($this->lockedParams['support_email']),
                ]
            )
            ->add(
                'domainName',
                TextType::class,
                [
                    'label' => 'domain_name',
                    'disabled' => isset($this->lockedParams['domain_name']),
                    'constraints' => new DomainName(),
                    'required' => false,
                ]
            )
            ->add(
                'sslEnabled',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => 'ssl_enabled',
                    'disabled' => isset($this->lockedParams['ssl_enabled']),
                ]
            )
            ->add(
                'allowSelfRegistration',
                CheckboxType::class,
                [
                    'required' => false,
                    'disabled' => isset($this->lockedParams['allow_self_registration']),
                    'label' => 'self_registration',
                ]
            )
            ->add(
                'registerButtonAtLogin',
                CheckboxType::class,
                [
                    'required' => false,
                    'disabled' => isset($this->lockedParams['register_button_at_login']),
                    'label' => 'show_register_button_in_login_page',
                ]
            )
            ->add(
                'defaultRole',
                'entity',
                [
                    'mapped' => false,
                    'data' => $this->role,
                    'class' => 'Claroline\CoreBundle\Entity\Role',
                    'choice_translation_domain' => true,
                    'expanded' => false,
                    'multiple' => false,
                    'property' => 'translationKey',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                                ->where('r.type = '.Role::PLATFORM_ROLE)
                                ->andWhere("r.name != 'ROLE_ANONYMOUS'");
                    },
                    'disabled' => isset($this->lockedParams['default_role']),
                    'label' => 'default_role',
                ]
            )
            ->add(
                'localeLanguage',
                ChoiceType::class,
                [
                    'choices' => $this->langs,
                    'disabled' => isset($this->lockedParams['locale_language']),
                    'label' => 'default_language',
                ]
            )
            ->add(
                'formCaptcha',
                CheckboxType::class,
                [
                    'label' => 'display_captcha',
                    'required' => false,
                    'disabled' => isset($this->lockedParams['form_captcha']),
                ]
            )
            ->add(
                'formHoneypot',
                CheckboxType::class,
                [
                    'label' => 'use_honeypot',
                    'required' => false,
                    'disabled' => isset($this->lockedParams['form_honeypot']),
                ]
            )
            ->add(
                'loginTargetRoute',
                ChoiceType::class,
                [
                    'choices' => $this->targetLoginUrls,
                    'choices_as_values' => true,
                    'expanded' => false,
                    'multiple' => false,
                    'label' => 'login_target_route_option',
                ]
            )
            ->add(
                'redirectAfterLoginOption',
                ChoiceType::class,
                [
                    'choices' => $this->buildRedirectOptions(),
                    'attr' => [
                        'class' => 'redirect-after-login',
                    ],
                    'choices_as_values' => true,
                    'expanded' => true,
                    'multiple' => false,
                    'label' => 'redirect_after_login_option',
                ]
            )
            ->add(
                'redirectAfterLoginUrl',
                TextType::class,
                [
                    'label' => 'redirect_after_login_url',
                    'required' => false,
                ]
            )
            ->add(
                'accountDuration',
                IntegerType::class,
                [
                    'label' => 'account_duration_label',
                    'required' => false,
                ]
            )
            ->add(
                'anonymousPublicProfile',
                CheckboxType::class,
                [
                    'label' => 'show_profile_for_anonymous',
                    'required' => false,
                    'disabled' => isset($this->lockedParams['anonymous_public_profile']),
                ]
            )
            ->add(
                'portfolioUrl',
                UrlType::class,
                [
                    'label' => 'portfolio_url',
                    'required' => false,
                    'disabled' => isset($this->lockedParams['portfolio_url']),
                ]
            )
            ->add(
                'isNotificationActive',
                CheckboxType::class,
                [
                    'label' => 'activate_notifications',
                    'required' => false,
                    'disabled' => isset($this->lockedParams['is_notification_active']),
                ]
            )
            ->add(
                'maxStorageSize',
                TextType::class,
                [
                    'required' => false,
                    'label' => 'max_storage_size',
                    'constraints' => [new FileSize()],
                    'disabled' => isset($this->lockedParams['max_storage_size']),
                ]
            )
            ->add(
                'maxUploadResources',
                IntegerType::class,
                [
                    'required' => false,
                    'label' => 'count_resources',
                    'disabled' => isset($this->lockedParams['max_upload_resources']),
                ]
            )
            ->add(
                'maxWorkspaceUsers',
                IntegerType::class,
                [
                    'required' => false,
                    'label' => 'workspaces_max_users',
                    'disabled' => isset($this->lockedParams['max_workspace_users']),
                ]
            )
            ->add(
                'showHelpButton',
                CheckboxType::class,
                [
                    'label' => 'show_help_button',
                    'required' => false,
                    'disabled' => isset($this->lockedParams['show_help_button']),
                ]
            )
            ->add(
                'helpUrl',
                TextType::class,
                [
                    'label' => 'help_url',
                    'required' => false,
                    'disabled' => isset($this->lockedParams['help_url']),
                ]
            )
            ->add(
                'sendMailAtWorkspaceRegistration',
                CheckboxType::class,
                [
                    'required' => false,
                    'disabled' => isset($this->lockedParams['send_mail_at_workspace_registration']),
                    'label' => 'send_mail_at_workspace_registration',
                ]
            )
            ->add(
                'registrationMailValidation',
                ChoiceType::class,
                [
                    'disabled' => isset($this->lockedParams['registration_mail_validation']),
                    'label' => 'registration_mail_validation',
                    'choices' => [
                        PlatformDefaults::REGISTRATION_MAIL_VALIDATION_PARTIAL => 'send_mail_info',
                        PlatformDefaults::REGISTRATION_MAIL_VALIDATION_FULL => 'force_mail_validation',
                    ],
                ]
            )
            ->add(
                'defaultWorkspaceTag',
                TextType::class,
                [
                    'label' => 'default_workspace_tag',
                    'required' => false,
                    'disabled' => isset($this->lockedParams['default_workspace_tag']),
                ]
            )
            ->add(
                'enableOpengraph',
                CheckboxType::class,
                [
                    'label' => 'enable_opengraph',
                    'required' => false,
                    'disabled' => isset($this->lockedParams['default_workspace_tag']),
                ]
            )
            ->add(
                'isPdfExportActive',
                CheckboxType::class,
                [
                    'label' => 'activate_pdf_export',
                    'required' => false,
                    'disabled' => isset($this->lockedParams['is_pdf_export_active']),
                ]
            )
            ->add(
                'tmpDir',
                TextType::class,
                [
                    'label' => 'temporary_directory',
                    'disabled' => isset($this->lockedParams['tmp_dir']),
                ]
            );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();

            $form
                ->add(
                    'platformInitDate',
                    'datepicker',
                    [
                        'input' => 'timestamp',
                        'label' => 'platform_init_date',
                        'required' => false,
                        'format' => $this->dateFormat,
                        'language' => $this->language,
                        'disabled' => isset($this->lockedParams['platform_init_date']),
                    ]
                )
                ->add(
                    'platformLimitDate',
                    'datepicker',
                    [
                        'input' => 'timestamp',
                        'label' => 'platform_expiration_date',
                        'required' => false,
                        'format' => $this->dateFormat,
                        'language' => $this->language,
                        'disabled' => isset($this->lockedParams['platform_limit_date']),
                    ]
                );
        });
    }

    public function getName()
    {
        return 'platform_parameters_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                'translation_domain' => 'platform',
                'date_format' => DateType::HTML5_FORMAT,
            ]
        );
    }

    private function buildRedirectOptions()
    {
        $options = PlatformDefaults::$REDIRECT_OPTIONS;
        $choices = [];

        foreach ($options as $option) {
            $choices['redirect_after_login_option_'.strtolower($option)] = $option;
        }

        return $choices;
    }
}
