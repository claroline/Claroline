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
            array('required' => true)
        );
        $attr = array();
        $attr['class'] = 'datepicker input-small';
        $attr['data-date-format'] = 'dd-mm-yyyy';
        $attr['autocomplete'] = 'off';
        $builder->add(
            'start_date',
            'datepicker',
            array(
                'required' => false,
                'format' => 'dd-MM-yyyy',
                'widget' => 'single_text',
                'attr' => $attr,
                'input' => 'datetime'
            )
        );
        $builder->add(
            'end_date',
            'datepicker',
            array(
                'required' => false,
                'format' => 'dd-MM-yyyy',
                'widget' => 'single_text',
                'attr' => $attr,
                'input' => 'datetime'
            )
        );
        $builder->add(
            'sessionStatus',
            'choice',
            array(
                'required' => true,
                'choices' => array (
                    0 => 'session_not_started',
                    1 => 'session_open',
                    2 => 'session_closed'
                )
            )
        );
        $builder->add(
            'defaultSession',
            'checkbox',
            array('required' => true)
        );
        $builder->add(
            'publicRegistration',
            'checkbox',
            array('required' => true)
        );
        $builder->add(
            'publicUnregistration',
            'checkbox',
            array('required' => true)
        );
        $builder->add(
            'cursus',
            'entity',
            array(
                'required' => false,
                'class' => 'ClarolineCursusBundle:Cursus',
                'query_builder' => function (EntityRepository $er) {

                    return $er->createQueryBuilder('c')
                        ->where('c.parent IS NULL')
                        ->orderBy('c.title', 'ASC');
                },
                'property' => 'title',
                'multiple' => true,
                'expanded' => true
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
            array('required' => true)
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
        return 'course_session_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'cursus'));
    }
}
