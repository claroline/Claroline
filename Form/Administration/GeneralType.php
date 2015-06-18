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

use Claroline\CoreBundle\Validator\Constraints\FileSize;
use Claroline\CoreBundle\Entity\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GeneralType extends AbstractType
{
    private $langs;
    private $role;
    private $description;
    private $dateFormat;
    private $language;
    private $lockedParams;

    public function __construct(
        array $langs,
        $role,
        $description,
        $dateFormat,
        $language,
        array $lockedParams = array()
    )
    {
        $this->role = $role;
        $this->description = $description;
        $this->dateFormat  = $dateFormat;
        $this->language    = $language;

        if (!empty($langs)) {
            $this->langs = $langs;
        } else {
            $this->langs = array('en' => 'en', 'fr' => 'fr');
        }
        $this->lockedParams = $lockedParams;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                'text',
                array(
                    'required' => false,
                    'disabled' => isset($this->lockedParams['name']),
                    'label' => 'name'
                )
            )
            ->add(
                'description',
                'content',
                array(
                    'data' => $this->description,
                    'mapped' => false,
                    'required' => false,
                    'label' => 'description',
                    'theme_options' => array('contentTitle' => false, 'tinymce' => false)
                )
            )
            ->add(
                'support_email',
                'email',
                array(
                    'label' => 'support_email',
                    'disabled' => isset($this->lockedParams['support_email'])
                )
            )
            ->add(
                'selfRegistration',
                'checkbox',
                array(
                    'required' => false,
                    'disabled' => isset($this->lockedParams['allow_self_registration']),
                    'label' => 'self_registration'
                )
            )
            ->add(
                'registerButtonAtLogin',
                'checkbox',
                array(
                    'required' => false,
                    'disabled' => isset($this->lockedParams['register_button_at_login']),
                    'label' => 'show_register_button_in_login_page'
                )
            )
            ->add(
                'defaultRole',
                'entity',
                array(
                    'mapped' => false,
                    'data' => $this->role,
                    'class' => 'Claroline\CoreBundle\Entity\Role',
                    'choice_translation_domain' => true,
                    'expanded' => false,
                    'multiple' => false,
                    'property' => 'translationKey',
                    'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                                ->where("r.type = " . Role::PLATFORM_ROLE)
                                ->andWhere("r.name != 'ROLE_ANONYMOUS'");
                    },
                    'disabled' => isset($this->lockedParams['default_role']),
                    'label' => 'default_role'
                )
            )
            ->add(
                'localeLanguage',
                'choice',
                array(
                    'choices' => $this->langs,
                    'disabled' => isset($this->lockedParams['locale_language']),
                    'label' => 'default_language'
                )
            )
            ->add(
                'formCaptcha',
                'checkbox',
                array(
                    'label' => 'display_captcha',
                    'required' => false,
                    'disabled' => isset($this->lockedParams['form_captcha'])
                )
            )
            ->add(
                'redirect_after_login',
                'checkbox',
                array(
                    'label' => 'redirect_after_login',
                    'required' => false,
                    'disabled' => isset($this->lockedParams['redirect_after_login'])
                )
            )
            ->add(
                'account_duration',
                'integer',
                array(
                    'label' => 'account_duration_label',
                    'required' => false,
                    'disabled' => isset($this->lockedParams['account_duration'])
                )
            )
            ->add(
                'anonymous_public_profile',
                'checkbox',
                array(
                    'label' => 'show_profile_for_anonymous',
                    'required' => false,
                    'disabled' => isset($this->lockedParams['anonymous_public_profile'])
                )
            )
            ->add(
                'portfolio_url',
                'url',
                array(
                    'label' => 'portfolio_url',
                    'required' => false,
                    'disabled' => isset($this->lockedParams['portfolio_url'])
                )
            )
            ->add(
                'isNotificationActive',
                'checkbox',
                array(
                    'label' => 'activate_notifications',
                    'required' => false,
                    'disabled' => isset($this->lockedParams['is_notification_active'])
                )
            )
            ->add(
                'maxStorageSize',
                'text',
                array(
                    'required' => false,
                    'label' => 'max_storage_size',
                    'constraints' => array(new FileSize()),
                    'disabled' => isset($this->lockedParams['max_storage_size'])
                )
            )
            ->add(
                'maxUploadResources',
                'integer',
                array(
                    'required' => false,
                    'label' => 'count_resources',
                    'disabled' => isset($this->lockedParams['max_upload_resources'])
                )
            )
            ->add(
                'workspaceMaxUsers',
                'integer',
                array(
                    'required' => false,
                    'label' => 'workspace_max_users',
                    'disabled' => isset($this->lockedParams['max_workspace_users'])
                )
            )
            ->add(
                'showHelpButton',
                'checkbox',
                array(
                    'label' => 'show_help_button',
                    'required' => false,
                    'disabled' => isset($this->lockedParams['show_help_button'])
                )
            )
            ->add(
                'helpUrl',
                'text',
                array(
                    'label' => 'help_url',
                    'required' => false,
                    'disabled' => isset($this->lockedParams['help_url'])
                )
            );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
            /** @var \Claroline\CoreBundle\Library\Configuration\PlatformConfiguration $generalParameters */
            $generalParameters = $event->getData();
            $form = $event->getForm();

            $form
                ->add(
                    'platform_init_date',
                    'datepicker',
                    array(
                        'input'       => 'timestamp',
                        'label'       => 'platform_init_date',
                        'required'    => false,
                        'format'      => $this->dateFormat,
                        'language'    => $this->language,
                        'disabled' => isset($this->lockedParams['platform_init_date'])
                    )
                )
                ->add(
                    'platform_limit_date',
                    'datepicker',
                    array(
                        'input'       => 'timestamp',
                        'label'       => 'platform_expiration_date',
                        'required'    => false,
                        'format'      => $this->dateFormat,
                        'language'    => $this->language,
                        'disabled' => isset($this->lockedParams['platform_limit_date'])
                    )
                );
        });
   }

    public function getName()
    {
        return 'platform_parameters_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'translation_domain' => 'platform',
                'date_format'        => DateType::HTML5_FORMAT
            )
        );
    }
}
