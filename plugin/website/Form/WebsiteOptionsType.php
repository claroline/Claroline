<?php

namespace Icap\WebsiteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WebsiteOptionsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('copyrightEnabled')
            ->add('copyrightText')
            ->add('analyticsProvider')
            ->add('analyticsAccountId')
            ->add('cssCode')
            ->add('bgColor')
            ->add('bgContentColor')
            ->add('bgRepeat')
            ->add('bgPosition')
            ->add('bannerBgColor')
            ->add('bannerBgRepeat')
            ->add('bannerBgPosition')
            ->add('bannerHeight')
            ->add('bannerEnabled')
            ->add('bannerText')
            ->add('footerBgColor')
            ->add('footerBgRepeat')
            ->add('footerBgPosition')
            ->add('footerHeight')
            ->add('footerEnabled')
            ->add('footerText')
            ->add('menuBgColor')
            ->add('sectionBgColor')
            ->add('sectionFontColor')
            ->add('menuBorderColor')
            ->add('menuFontColor')
            ->add('menuHoverColor')
            ->add('menuFontFamily')
            ->add('menuFontStyle')
            ->add('menuFontWeight')
            ->add('menuFontSize')
            ->add('menuWidth')
            ->add('menuOrientation')
            ->add('totalWidth')
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Icap\WebsiteBundle\Entity\WebsiteOptions',
            'translation_domain' => 'icap_website',
            'csrf_protection' => false,
            'intention' => 'update_website_options',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'icap_website_options_type';
    }
}
