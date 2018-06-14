<?php

namespace Icap\DropzoneBundle\Form;

use Claroline\CoreBundle\Form\Field\TinymceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DropzoneCriteriaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('goBack', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('correctionInstruction', TinymceType::class, ['required' => false])
            ->add('totalCriteriaColumn', NumberType::class, ['required' => true])
            ->add('allowCommentInCorrection', CheckboxType::class, ['required' => false])
            ->add('forceCommentInCorrection', CheckboxType::class, ['required' => false])
            ->add('recalculateGrades', HiddenType::class, ['mapped' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'icap_dropzone',
        ]);
    }
}
