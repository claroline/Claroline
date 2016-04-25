<?php

namespace Icap\PortfolioBundle\Form\Type;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @DI\FormType
 */
class RenamePortfolioType extends PortfolioTitleType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('refreshUrl', 'checkbox',
                array(
                    'required' => false,
                    'mapped' => false,
                )
            );
    }

    public function getName()
    {
        return 'icap_portfolio_rename_form';
    }
}
