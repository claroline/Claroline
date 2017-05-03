<?php

namespace Icap\BibliographyBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookReferenceConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'api_key',
            'text',
            [
                'required' => false,
                'label' => 'api_key',
                'attr' => [
                    'autofocus' => true,
                ],
            ])
            ->add('submit', 'submit', [
                'label' => 'submit_config_label',
                'translation_domain' => 'icap_bibliography',
                'attr' => [
                    'class' => 'btn btn-primary pull-right',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults($this->getDefaultOptions());

        return $this;
    }

    public function getDefaultOptions()
    {
        return [
          'data_class' => 'Icap\BibliographyBundle\Entity\BookReferenceConfiguration',
          'translation_domain' => 'icap_bibliography',
      ];
    }

    public function getName()
    {
        return 'icap_bibliography_configuration';
    }
}
