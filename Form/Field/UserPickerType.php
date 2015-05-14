<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Field;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\DataTransformer\UserPickerTransfromer;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("claroline.form.user_picker")
 * @DI\FormType(alias = "userpicker")
 */
class UserPickerType extends AbstractType
{
    private $translator;
    private $userManager;
    private $userPickerTransformer;

    /**
     * @DI\InjectParams({
     *     "translator"            = @DI\Inject("translator"),
     *     "userManager"           = @DI\Inject("claroline.manager.user_manager"),
     *     "userPickerTransformer" = @DI\Inject("claroline.transformer.user_picker")
     * })
     */
    public function __construct(
        TranslatorInterface $translator,
        UserManager $userManager,
        UserPickerTransfromer $userPickerTransformer
    )
    {
        $this->translator = $translator;
        $this->userManager = $userManager;
        $this->userPickerTransformer = $userPickerTransformer;
    }

    public function getName()
    {
        return 'userpicker';
    }

    public function getParent()
    {
        return 'text';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->userPickerTransformer);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['picker_name'] = $options['picker_name'];
        $view->vars['picker_title'] = $options['picker_title'];
        $view->vars['multiple'] = $options['multiple'];
        $view->vars['show_all_users'] = $options['show_all_users'];
        $view->vars['show_filters'] = $options['show_filters'];
        $view->vars['show_username'] = $options['show_username'];
        $view->vars['show_mail'] = $options['show_mail'];
        $view->vars['show_code'] = $options['show_code'];
        $view->vars['blacklist'] = $options['blacklist'];
        $view->vars['whitelist'] = $options['whitelist'];
        $view->vars['selected_users'] = $options['selected_users'];
        $view->vars['forced_groups'] = $options['forced_groups'];
        $view->vars['forced_roles'] = $options['forced_roles'];
        $view->vars['forced_workspaces'] = $options['forced_workspaces'];
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'translation_domain' => 'platform',
                'picker_name' => 'picker-name',
                'picker_title' => $this->translator->trans(
                    'user_selector',
                    array(),
                    'platform'
                ),
                'multiple' => false,
                'show_all_users' => false,
                'show_filters' => true,
                'show_username' => true,
                'show_mail' => false,
                'show_code' => false,
                'blacklist' => array(),
                'whitelist' => array(),
                'selected_users' => array(),
                'forced_groups' => array(),
                'forced_roles' => array(),
                'forced_workspaces' => array()
            )
        );
    }
}
