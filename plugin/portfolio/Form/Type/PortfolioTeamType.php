<?php

namespace Icap\PortfolioBundle\Form\Type;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @DI\FormType
 */
class PortfolioTeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('team', 'zenstruck_ajax_entity',
                array(
                    'class' => 'ClarolineTeamBundle:Team',
                    'use_controller' => true,
                    'property' => 'name',
                )
            );
    }

    public function getName()
    {
        return 'icap_portfolio_visible_team_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Icap\PortfolioBundle\Entity\PortfolioTeam',
                'translation_domain' => 'icap_portfolio',
            )
        );
    }
}
