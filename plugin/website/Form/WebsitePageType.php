<?php

namespace Icap\WebsiteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WebsitePageType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'title',
                'text'
            )
            ->add(
                'type',
                'text'
            )
            ->add(
                'description',
                'text'
            )
            ->add(
                'visible',
                'checkbox'
            )
            ->add(
                'isSection',
                'checkbox'
            )
            ->add(
                'richText',
                'textarea'
            )
            ->add(
                'url',
                'url'
            )
            ->add(
                'target',
                'choice',
                array(
                    'choices' => array(
                        'embed' => 0,
                        'new_window' => 1,
                    ),
                    'choices_as_values' => true,
                )
            )
            ->add(
                'resourceNode',
                'entity',
                array(
                    'class' => 'ClarolineCoreBundle:Resource\ResourceNode',
                    'choice_label' => 'id',
                )
            )
            ->add(
                'resourceNodeType',
                'text'
            )
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'icap_website_page_type';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Icap\WebsiteBundle\Entity\WebsitePage',
            'translation_domain' => 'icap_website',
            'csrf_protection' => false,
            'intention' => 'create_website_page',
        ));
    }
}
