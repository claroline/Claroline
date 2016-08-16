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

use Claroline\CursusBundle\Manager\CursusManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class CourseSessionType extends AbstractType
{
    private $cursusManager;
    private $translator;

    public function __construct(CursusManager $cursusManager, TranslatorInterface $translator)
    {
        $this->cursusManager = $cursusManager;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $validatorsRoles = $this->cursusManager->getValidatorsRoles();
        $builder->add(
            'name',
            'text',
            ['required' => true]
        );
        $builder->add(
            'description',
            'textarea',
            ['required' => false, 'label' => 'description', 'translation_domain' => 'platform']
        );
        $builder->add(
            'startDate',
            'datetime',
            [
                'required' => true,
                'input' => 'datetime',
                'format' => 'dd/MM/yyy HH:mm',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'date',
                    'with_seconds' => false,
                    'data_timezone' => 'Europe/Brussels',
                    'user_timezone' => 'Europe/Brussels',
                ],
                'constraints' => [new DateTime(), new NotBlank()],
                'translation_domain' => 'platform',
                'label' => 'start_date',
            ]
        );
        $builder->add(
            'endDate',
            'datetime',
            [
                'required' => true,
                'input' => 'datetime',
                'format' => 'dd/MM/yyy HH:mm',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'date',
                    'with_seconds' => false,
                    'data_timezone' => 'Europe/Brussels',
                    'user_timezone' => 'Europe/Brussels',
                ],
                'constraints' => [new DateTime(), new NotBlank()],
                'translation_domain' => 'platform',
                'label' => 'end_date',
            ]
        );
        $builder->add(
            'defaultSession',
            'choice',
            [
                'choices' => ['yes' => true, 'no' => false],
                'choices_as_values' => true,
                'required' => true,
                'label' => 'default_session',
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
            'cursus',
            'entity',
            [
                'required' => false,
                'class' => 'ClarolineCursusBundle:Cursus',
                'query_builder' => function (EntityRepository $er) {

                    return $er->createQueryBuilder('c')
                        ->where('c.parent IS NULL')
                        ->orderBy('c.title', 'ASC');
                },
                'property' => 'title',
                'multiple' => true,
                'expanded' => true,
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
        return 'course_session_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'cursus']);
    }
}
