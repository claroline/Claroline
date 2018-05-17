<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 2/20/15
 */

namespace Icap\InwicastBundle\Form;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MediacenterType.
 *
 * @DI\FormType;
 */
class MediaCenterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(UrlType::class, UrlType::class, ['required' => true])
            ->add('driver', TextType::class, ['required' => true])
            ->add('host', TextType::class, ['required' => true])
            ->add('port', TextType::class, ['required' => true])
            ->add('dbname', TextType::class, ['required' => true])
            ->add('user', TextType::class, ['required' => true])
            ->add('password', 'password', ['required' => true]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'inwicast_plugin_type_mediacenter';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'widget',
                'data_class' => 'Icap\InwicastBundle\Entity\MediaCenter',
                'csrf_protection' => true,
            ]
        );
    }
}
