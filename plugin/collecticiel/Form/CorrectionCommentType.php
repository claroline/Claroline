<?php

namespace Innova\CollecticielBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CorrectionCommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['allowCommentInCorrection'] == true && $options['edit'] === true) {
            $builder->add('comment', 'tinymce', array('required' => false));
        }

        $builder
            ->add('goBack', 'hidden', array('mapped' => false));
    }

    public function getName()
    {
        return 'innova_collecticiel_add_comment_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'edit' => true,
            'allowCommentInCorrection' => false,
            'translation_domain' => 'innova_collecticiel',
        ));
    }
}
