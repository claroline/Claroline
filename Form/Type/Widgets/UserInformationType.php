<?php

namespace Icap\PortfolioBundle\Form\Type\Widgets;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @DI\FormType
 */
class UserInformationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('city', 'text');
    }

    public function getName()
    {
        return 'icap_portfolio_widget_form_userInformation';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'Icap\PortfolioBundle\Entity\Widget\UserInformationWidget',
                'translation_domain' => 'icap_portfolio',
                'csrf_protection'    => false,
            )
        );
    }
}
