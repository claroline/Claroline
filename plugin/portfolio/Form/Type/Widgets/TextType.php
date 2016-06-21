<?php

namespace Icap\PortfolioBundle\Form\Type\Widgets;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @DI\FormType
 */
class TextType extends AbstractWidgetType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('text', 'tinymce');
    }

    public function getName()
    {
        return 'icap_portfolio_widget_form_text';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Icap\PortfolioBundle\Entity\Widget\TextWidget',
                'translation_domain' => 'icap_portfolio',
                'csrf_protection' => false,
            )
        );
    }
}
