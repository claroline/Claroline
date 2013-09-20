<?php
/**
 * Created by JetBrains PhpStorm.
 * User: gaetan
 * Date: 28/06/13
 * Time: 15:31
 * To change this template use File | Settings | File Templates.
 */
namespace Icap\LessonBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LessonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',        'text');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Icap\LessonBundle\Entity\Lesson'
        ));
    }

    public function getName()
    {
        return 'icap_lesson_type';
    }
}