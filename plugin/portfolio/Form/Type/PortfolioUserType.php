<?php

namespace Icap\PortfolioBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @DI\FormType
 */
class PortfolioUserType extends AbstractType
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
        return 'icap_portfolio_visible_user_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'Icap\PortfolioBundle\Entity\PortfolioUser',
                'translation_domain' => 'icap_portfolio'
            )
        );
    }
}
