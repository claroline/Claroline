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
                TextType::class
            )
            ->add(
                'type',
                TextType::class
            )
            ->add(
                'description',
                TextType::class
            )
            ->add(
                'visible',
                CheckboxType::class
            )
            ->add(
                'isSection',
                CheckboxType::class
            )
            ->add(
                'richText',
                'textarea'
            )
            ->add(
                UrlType::class,
                'url'
            )
            ->add(
                'target',
                ChoiceType::class,
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
                TextType::class
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
