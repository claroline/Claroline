<?php

namespace FormaLibre\SupportBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StatusType extends AbstractType
{
    private $isLocked;

    public function __construct($isLocked = false)
    {
        $this->isLocked = $isLocked;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            TextType::class,
            [
                'required' => true,
                'label' => 'name',
                'translation_domain' => 'platform',
            ]
        );
        $builder->add(
            'code',
            TextType::class,
            [
                'required' => true,
                'label' => 'code',
                'translation_domain' => 'platform',
                'read_only' => $this->isLocked,
            ]
        );
        $builder->add(
            'description',
            'tinymce',
            [
                'required' => true,
                'label' => 'description',
                'translation_domain' => 'platform',
            ]
        );
    }

    public function getName()
    {
        return 'status_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'support']);
    }
}
