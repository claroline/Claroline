<?php

namespace Icap\PortfolioBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @DI\FormType
 */
class PortfolioEvaluatorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', 'zenstruck_ajax_entity',
                array(
                    'class'          => 'ClarolineCoreBundle:User',
                    'use_controller' => true,
                    'property'       => 'username',
                    'repo_method'    => 'findByNameForAjax'
                )
            );
    }

    public function getName()
    {
        return 'icap_portfolio_evaluator_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'Icap\PortfolioBundle\Entity\PortfolioEvaluator',
                'translation_domain' => 'icap_portfolio'
            )
        );
    }
}
