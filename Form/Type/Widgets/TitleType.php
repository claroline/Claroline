<?php

namespace Icap\PortfolioBundle\Form\Type\Widgets;

use Icap\PortfolioBundle\Form\Type\PortfolioType;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @DI\FormType
 */
class TitleType extends PortfolioType
{
    public function getName()
    {
        return 'icap_portfolio_widget_form_title';
    }
}