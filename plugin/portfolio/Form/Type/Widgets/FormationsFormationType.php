<?php

namespace Icap\PortfolioBundle\Form\Type\Widgets;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @DI\FormType
 */
class FormationsFormationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', IntegerType::class, array(
                'required' => false,
                'mapped' => false,
            ))
            ->add('name', TextType::class, array(
                'required' => false,
                'mapped' => false,
            ))
            ->add('resource', 'entity', array(
                'class' => 'ClarolineCoreBundle:Resource\ResourceNode',
                'property' => 'name',
                'required' => false,
            ))
            ->add('uri', TextType::class, array(
                'required' => false,
                'mapped' => false,
            ))
            ->add('uriLabel', TextType::class, array(
                'required' => false,
                'mapped' => false,
            ));
    }

    public function getName()
    {
        return 'icap_portfolio_widget_form_formations_formation';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Icap\PortfolioBundle\Entity\Widget\FormationsWidgetResource',
                'translation_domain' => 'icap_portfolio',
                'csrf_protection' => false,
            )
        );
    }
}
