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

class DeleteChapterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text', array('disabled' => true));

        if ($options['hasChildren'] == true){
            $builder ->add('children', 'checkbox', array(
                'label' => 'Voulez-vous supprimer les sous-chapitres ?',
                'required' => false,
                'mapped' => false
            ));
        } else {
            $builder ->add('children', 'hidden', array(
                'required' => false,
                'mapped' => false
            ));
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ICAP\LessonBundle\Entity\Chapter',
            'hasChildren' => true
        ));
    }

    public function getName()
    {
        return 'icap_lesson_deletechaptertype';
    }
}