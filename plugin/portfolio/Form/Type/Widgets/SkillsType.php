<?php

namespace Icap\PortfolioBundle\Form\Type\Widgets;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @DI\FormType
 */
class SkillsType extends AbstractWidgetType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('children', 'collection',
                array(
                    'type' => 'icap_portfolio_widget_form_skills_skill',
                    'by_reference' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'property_path' => 'skills',
                )
            );
    }

    public function getName()
    {
        return 'icap_portfolio_widget_form_skills';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Icap\PortfolioBundle\Entity\Widget\SkillsWidget',
                'translation_domain' => 'icap_portfolio',
                'csrf_protection' => false,
            )
        );
    }
}
