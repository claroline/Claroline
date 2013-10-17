<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nicolas
 * Date: 17/10/13
 * Time: 09:57
 * To change this template use File | Settings | File Templates.
 */

namespace Icap\LessonBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DuplicateChapterType extends AbstractType
{
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options){

        $chapters = array();
        $root = true;
        foreach ($options['chapters'] as $child) {
            if($root){
                $chapters[$child->getId()] = 'Racine';
                $root = false;
            }else{
                $chapters[$child->getId()] = $child->getTitle();
            }
        }

        $builder
            ->add('parent', 'choice',
                array(
                    'mapped' => false,
                    'choices' => $chapters
                )
            )
            ->add('duplicate_children', 'checkbox',
                array(
                    'mapped' => false,
                    'required' => false
                )
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Icap\LessonBundle\Entity\Chapter',
            'chapters' => array()
        ));
    }

    public function getName()
    {
        return 'icap_lesson_duplicatechaptertype';
    }
}