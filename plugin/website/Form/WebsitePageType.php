<?php

namespace Icap\WebsiteBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
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
                TextareaType::class
            )
            ->add(
                'url',
                UrlType::class
            )
            ->add(
                'target',
                ChoiceType::class,
                [
                    'choices' => [
                        'embed' => 0,
                        'new_window' => 1,
                    ],
                    'choices_as_values' => true,
                ]
            )
            ->add(
                'resourceNode',
                EntityType::class,
                [
                    'class' => 'ClarolineCoreBundle:Resource\ResourceNode',
                    'choice_label' => 'id',
                ]
            )
            ->add(
                'resourceNodeType',
                TextType::class
            )
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Icap\WebsiteBundle\Entity\WebsitePage',
            'translation_domain' => 'icap_website',
            'csrf_protection' => false,
            'intention' => 'create_website_page',
        ]);
    }
}
