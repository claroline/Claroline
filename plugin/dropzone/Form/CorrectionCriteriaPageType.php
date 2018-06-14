<?php

namespace Icap\DropzoneBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CorrectionCriteriaPageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $criteria = $options['criteria'];
        $totalChoice = $options['totalChoice'];

        $choices = [];
        for ($i = 0; $i < $totalChoice; ++$i) {
            $choices[$i] = $i;
        }

        foreach ($criteria as $criterion) {
            $params = [
                'choices' => $choices,
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                'label' => $criterion->getInstruction(),
                'label_attr' => ['style' => 'font-weight: normal;'],
            ];

            if (false === $options['edit']) {
                $params['disabled'] = 'disabled';
            }

            $builder
                ->add('goBack', HiddenType::class, ['mapped' => false])
                ->add($criterion->getId(), ChoiceType::class, $params);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'totalChoice' => 5,
            'criteria' => [],
            'edit' => true,
            'translation_domain' => 'icap_dropzone',
        ]);
    }
}
