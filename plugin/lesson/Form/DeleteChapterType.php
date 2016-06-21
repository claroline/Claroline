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
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeleteChapterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['hasChildren'] == true) {
            $builder->add('deletechildren', 'checkbox', array(
                'required' => false,
                'mapped' => false,
            ));
        } else {
            /*            $builder ->add('children', 'hidden', array(
                'required' => false,
                'mapped' => false
            ));*/
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Icap\LessonBundle\Entity\Chapter',
            'hasChildren' => true,
            'no_captcha' => true,
        ));
    }

    public function getName()
    {
        return 'icap_lesson_deletechaptertype';
    }
}
