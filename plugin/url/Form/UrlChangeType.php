<?php

namespace HeVinci\UrlBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UrlChangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $option)
    {
        $builder->add(
            UrlType::class,
            UrlType::class,
            array(
                'required' => true,
                'label' => 'Url',
                'constraints' => new Assert\NotBlank(),
                'attr' => [
                    'placeholder' => 'http://example.com',
                ],
            )
        );
    }

    public function getName()
    {
        return 'url_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}
