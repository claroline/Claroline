<?php

namespace Icap\PortfolioBundle\Form\Type;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @DI\FormType
 */
class PortfolioTitleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text',
                array(
                    'label'         => 'title',
                    'theme_options' => array('label_width' => 'col-md-2')
                )
            );
    }

    public function getName()
    {
        return 'icap_portfolio_title_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'Icap\PortfolioBundle\Entity\Widget\TitleWidget',
                'translation_domain' => 'icap_portfolio'
            )
        );
    }
}
