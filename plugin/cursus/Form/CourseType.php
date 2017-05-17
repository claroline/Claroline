<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Form;

use Claroline\CoreBundle\Entity\User;
use Claroline\CursusBundle\Manager\CursusManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class CourseType extends AbstractType
{
    private $cursusManager;
    private $translator;
    private $user;

    public function __construct(User $user, CursusManager $cursusManager, TranslatorInterface $translator)
    {
        $this->cursusManager = $cursusManager;
        $this->translator = $translator;
        $this->user = $user;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $validatorsRoles = $this->cursusManager->getValidatorsRoles();
        $workspaces = $this->cursusManager->getWorkspacesListForCurrentUser();

        $builder->add(
            'title',
            'text',
            [
                'required' => true,
                'label' => 'title',
                'translation_domain' => 'platform',
            ]
        );
        $builder->add(
            'code',
            'text',
            [
                'required' => true,
                'label' => 'code',
                'translation_domain' => 'platform',
            ]
        );
        $builder->add(
            'description',
            'textarea',
            [
                'required' => false,
                'label' => 'description',
                'translation_domain' => 'platform',
            ]
        );
        $builder->add(
            'icon',
            'file',
            [
                'required' => false,
                'mapped' => false,
                'label' => 'icon',
                'constraints' => [new Image()],
            ]
        );
        $builder->add(
            'publicRegistration',
            'choice',
            [
                'choices' => ['yes' => true, 'no' => false],
                'choices_as_values' => true,
                'required' => true,
                'label' => 'public_registration',
            ]
        );
        $builder->add(
            'publicUnregistration',
            'choice',
            [
                'choices' => ['yes' => true, 'no' => false],
                'choices_as_values' => true,
                'required' => true,
                'label' => 'public_unregistration',
            ]
        );
        $builder->add(
            'defaultSessionDuration',
            'text',
            [
                'required' => true,
                'constraints' => [new Range(['min' => 0]), new NotBlank()],
                'attr' => ['min' => 0],
                'label' => 'default_session_duration_label',
            ]
        );
        $builder->add(
            'withSessionEvent',
            'choice',
            [
                'choices' => ['yes' => true, 'no' => false],
                'choices_as_values' => true,
                'required' => true,
                'label' => 'with_session_event',
            ]
        );
        $builder->add(
            'workspace',
            'entity',
            [
                'class' => 'ClarolineCoreBundle:Workspace\Workspace',
                'choices' => $workspaces,
                'property' => 'name',
                'required' => false,
                'label' => 'workspace',
                'translation_domain' => 'platform',
                'multiple' => false,
            ]
        );
        $builder->add(
            'tutorRoleName',
            'text',
            [
                'required' => false,
                'attr' => ['class' => 'role-name-txt'],
                'label' => 'tutor_role_name',
            ]
        );
        $builder->add(
            'learnerRoleName',
            'text',
            [
                'required' => false,
                'attr' => ['class' => 'role-name-txt'],
                'label' => 'learner_role_name',
            ]
        );
        $builder->add(
            'maxUsers',
            'integer',
            [
                'required' => false,
                'constraints' => [new Range(['min' => 0])],
                'attr' => ['min' => 0],
                'label' => 'max_users',
            ]
        );
        $builder->add(
            'userValidation',
            'choice',
            [
                'choices' => ['yes' => true, 'no' => false],
                'choices_as_values' => true,
                'required' => true,
                'label' => 'user_validation',
            ]
        );
        $builder->add(
            'organizationValidation',
            'choice',
            [
                'choices' => ['yes' => true, 'no' => false],
                'choices_as_values' => true,
                'required' => true,
                'label' => 'organization_validation',
            ]
        );
        $builder->add(
            'registrationValidation',
            'choice',
            [
                'choices' => ['yes' => true, 'no' => false],
                'choices_as_values' => true,
                'required' => true,
                'label' => 'registration_validation',
            ]
        );
        $builder->add(
            'validators',
            'userpicker',
            [
                'required' => false,
                'picker_name' => 'validators-picker',
                'picker_title' => $this->translator->trans('validators_selection', [], 'cursus'),
                'multiple' => true,
                'attach_name' => false,
                'forced_roles' => $validatorsRoles,
                'label' => $this->translator->trans('validators', [], 'cursus'),
            ]
        );
    }

    public function getName()
    {
        return 'course_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'cursus']);
    }
}
