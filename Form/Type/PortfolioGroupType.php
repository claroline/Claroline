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
class PortfolioGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('group', 'zenstruck_ajax_entity',
                array(
                    'class'          => 'ClarolineCoreBundle:Group',
                    'use_controller' => true,
                    'property'       => 'name',
                    'repo_method'    => 'findByNameForAjax'
                )
            );
    }

    public function getName()
    {
        return 'icap_portfolio_visible_group_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'Icap\PortfolioBundle\Entity\PortfolioGroup',
                'translation_domain' => 'icap_portfolio'
            )
        );
    }
}
