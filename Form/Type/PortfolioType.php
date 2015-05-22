<?php

namespace Icap\PortfolioBundle\Form\Type;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @DI\FormType
 */
class PortfolioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('commentsViewAt', 'datetime', array(
                    'widget' => 'single_text'
                ))
            ->add('comments', 'text', array('mapped' => false))
            ->add('widgets', 'text', array('mapped' => false));
    }

    public function getName()
    {
        return 'icap_portfolio_portfolio_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'Icap\PortfolioBundle\Entity\Portfolio',
                'translation_domain' => 'icap_portfolio',
                'csrf_protection'    => false,
                'date_format'        => DateTimeType::HTML5_FORMAT
            )
        );
    }
}
