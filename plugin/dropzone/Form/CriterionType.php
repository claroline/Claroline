<?php

namespace Icap\DropzoneBundle\Form;

use Claroline\CoreBundle\Form\Field\TinymceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CriterionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('instruction', TinymceType::class, [])
            ->add('totalCriteriaColumn', HiddenType::class, [])
            ->add('allowCommentInCorrection', HiddenType::class, [])
            ->add('correctionsGlobalInstructions', HiddenType::class, ['mapped' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'icap_dropzone',
        ]);
    }
}
