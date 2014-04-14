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
            ->add('share_policy', 'choice', array(
                'choices'  => Portfolio::getSharePolicyLabels(),
                'required' => true,
                'expanded' => true,
                'label'    => 'visibility',
                'attr'     => array(
                    'class' => 'share_policies'
                )
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
