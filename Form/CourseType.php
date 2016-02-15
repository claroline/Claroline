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
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Range;

class CourseType extends AbstractType
{
    private $cursusManager;
    private $translator;
    private $user;

    public function __construct(
        User $user,
        CursusManager $cursusManager,
        TranslatorInterface $translator
    )
    {
        $this->cursusManager = $cursusManager;
        $this->translator = $translator;
        $this->user = $user;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->user;
        $validatorsRoles = $this->cursusManager->getValidatorsRoles();
        $workspaces = $this->cursusManager->getWorkspacesListForCurrentUser();

        $builder->add(
            'title',
            'text',
            array(
                'required' => true,
                'label' => 'title',
                'translation_domain' => 'platform'
            )
        );
        $builder->add(
            'code',
            'text',
            array(
                'required' => true,
                'label' => 'code',
                'translation_domain' => 'platform'
            )
        );
        $builder->add(
            'description',
            'tinymce',
            array(
                'required' => false,
                'label' => 'description',
                'translation_domain' => 'platform'
            )
        );
        $builder->add(
            'icon',
            'file',
            array(
                'required' => false,
                'mapped' => false,
                'label' => 'icon',
                'constraints' => array(new Image())
            )
        );
        $builder->add(
            'publicRegistration',
            'checkbox',
            array(
                'required' => true,
                'label' => 'public_registration'
            )
        );
        $builder->add(
            'publicUnregistration',
            'checkbox',
            array(
                'required' => true,
                'label' => 'public_unregistration'
            )
        );
        $builder->add(
            'workspace',
            'entity',
            array(
                'class' => 'ClarolineCoreBundle:Workspace\Workspace',
                'choices' => $workspaces,
                'property' => 'name',
                'required' => false,
                'label' => 'workspace',
                'translation_domain' => 'platform',
                'multiple' => false
            )
        );
        $builder->add(
            'workspaceModel',
            'entity',
            array(
                'class' => 'ClarolineCoreBundle:Model\WorkspaceModel',
                'query_builder' => function (EntityRepository $er) use ($user) {

                    return $er->createQueryBuilder('wm')
                        ->join('wm.users', 'u')
                        ->where('u.id = :userId')
                        ->setParameter('userId', $user->getId())
                        ->orderBy('wm.name', 'ASC');
                },
                'property' => 'name',
                'required' => false,
                'label' => 'workspace_model'
            )
        );
        $builder->add(
            'tutorRoleName',
            'text',
            array(
                'required' => false,
                'attr' => array('class' => 'role-name-txt'),
                'label' => 'tutor_role_name'
            )
        );
        $builder->add(
            'learnerRoleName',
            'text',
            array(
                'required' => false,
                'attr' => array('class' => 'role-name-txt'),
                'label' => 'learner_role_name'
            )
        );
        $builder->add(
            'maxUsers',
            'integer',
            array(
                'required' => false,
                'constraints' => array(
                    new Range(array('min' => 0))
                ),
                'attr' => array('min' => 0),
                'label' => 'max_users'
            )
        );
        $builder->add(
            'userValidation',
            'checkbox',
            array(
                'required' => true,
                'label' => 'user_validation'
            )
        );
        $builder->add(
            'registrationValidation',
            'checkbox',
            array(
                'required' => true,
                'label' => 'registration_validation'
            )
        );
        $builder->add(
            'validators',
            'userpicker',
            array(
                'required' => false,
                'picker_name' => 'validators-picker',
                'picker_title' => $this->translator->trans('validators_selection', array(), 'cursus'),
                'multiple' => true,
                'attach_name' => false,
                'forced_roles' => $validatorsRoles,
                'label' => $this->translator->trans('validators', array(), 'cursus')
            )
        );
    }

    public function getName()
    {
        return 'course_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'cursus'));
    }
}
