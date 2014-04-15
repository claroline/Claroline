<?php

namespace Icap\PortfolioBundle\Form\Type;

use Icap\PortfolioBundle\Entity\Portfolio;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @DI\FormType
 */
class VisibilityPortfolioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('visibility', 'choice', array(
                'choices'  => Portfolio::getVisibilityLabels(),
                'required' => true,
                'expanded' => true,
                'label'    => 'visibility'
            ))
            ->add('portfolio_users', 'collection', array(
                'type'          => 'icap_portfolio_visible_user_form',
                'by_reference'  => false,
                'attr'          => array('class' => 'rule-collections'),
                'theme_options' => array('label_width' => 'col-md-12'),
                'prototype'     => true,
                'allow_add'     => true,
                'allow_delete'  => true
            ));
    }

    public function getName()
    {
        return 'icap_portfolio_visibility_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'Icap\PortfolioBundle\Entity\Portfolio',
                'translation_domain' => 'icap_portfolio'
            )
        );
    }
}
