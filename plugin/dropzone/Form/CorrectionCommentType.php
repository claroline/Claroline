<?php

namespace Icap\DropzoneBundle\Form;

use Claroline\CoreBundle\Form\Field\TinymceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CorrectionCommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (true === $options['allowCommentInCorrection'] && true === $options['edit']) {
            $builder->add('comment', TinymceType::class, ['required' => false]);
        }

        $builder
            ->add('goBack', HiddenType::class, ['mapped' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'edit' => true,
            'allowCommentInCorrection' => false,
            'translation_domain' => 'icap_dropzone',
        ]);
    }
}
