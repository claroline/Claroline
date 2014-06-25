<?php

namespace Icap\PortfolioBundle\Form\Type\Widgets;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @DI\FormType
 */
class FormationsType extends AbstractWidgetType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('children', 'collection',
                array(
                    'type'          => 'icap_portfolio_widget_form_formations_formation',
                    'by_reference'  => false,
                    'allow_add'     => true,
                    'allow_delete'  => true,
                    'property_path' => 'formations'
                )
            );
    }

    public function getName()
    {
        return 'icap_portfolio_widget_form_formations';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'Icap\PortfolioBundle\Entity\Widget\FormationsWidget',
                'translation_domain' => 'icap_portfolio',
                'csrf_protection'    => false,
            )
        );
    }
}
