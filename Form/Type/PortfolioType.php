<?php

namespace Icap\PortfolioBundle\Form\Type;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @DI\FormType
 */
class PortfolioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('disposition', 'integer')
            ->add('widgets', 'text', array('mapped' => false));
    }

    public function getName()
    {
        return 'icap_portfolio_portfolio_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'Icap\PortfolioBundle\Entity\Portfolio',
                'translation_domain' => 'icap_portfolio',
                'csrf_protection'    => false,
            )
        );
    }
}
