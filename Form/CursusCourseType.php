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
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CursusCourseType extends AbstractType
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->user;

        $builder->add(
            'title',
            'text',
            array('required' => true)
        );
        $builder->add(
            'code',
            'text',
            array('required' => true)
        );
        $builder->add(
            'description',
            'tinymce',
            array('required' => false)
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
            'registrationValidation',
            'checkbox',
            array('required' => true)
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
                'required' => false
            )
        );
        $builder->add(
            'tutorRoleName',
            'text',
            array(
                'required' => false,
                'attr' => array('class' => 'role-name-txt')
            )
        );
        $builder->add(
            'learnerRoleName',
            'text',
            array(
                'required' => false,
                'attr' => array('class' => 'role-name-txt')
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
