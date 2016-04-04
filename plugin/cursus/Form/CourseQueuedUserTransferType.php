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

use Claroline\CursusBundle\Entity\Course;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CourseQueuedUserTransferType extends AbstractType
{
    private $course;

    public function __construct(Course $course)
    {
        $this->course = $course;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $course = $this->course;
        $builder->add(
            'session',
            'entity',
            array(
                'class' => 'ClarolineCursusBundle:CourseSession',
                'query_builder' => function (EntityRepository $er) use ($course) {

                    return $er->createQueryBuilder('s')
                        ->join('s.course', 'c')
                        ->where('c.id = :courseId')
                        ->andWhere('s.sessionStatus != 2')
                        ->setParameter('courseId', $course->getId())
                        ->orderBy('s.creationDate', 'ASC');
                },
                'label' => 'session',
                'property' => 'name',
                'required' => true,
                'mapped' => false
            )
        );
    }

    public function getName()
    {
        return 'course_queued_user_transfer_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'cursus'));
    }
}
