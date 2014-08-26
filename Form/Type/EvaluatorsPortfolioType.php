<?php

namespace Icap\PortfolioBundle\Form\Type;

use Icap\PortfolioBundle\Entity\Portfolio;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\FormType
 */
class EvaluatorsPortfolioType extends AbstractType
{
    /** @var \Symfony\Component\Translation\TranslatorInterface */
    private $translator;

    /**
     * @DI\InjectParams({
     *     "translator" = @DI\Inject("translator")
     * })
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('portfolio_users', 'collection', array(
                'type'          => 'icap_portfolio_evaluator_form',
                'by_reference'  => false,
                'attr'          => array('class' => 'rule-collections'),
                'theme_options' => array('label_width' => 'col-md-12'),
                'prototype'     => true,
                'allow_add'     => true,
                'allow_delete'  => true
            ))
            ->add('search_user', 'zenstruck_ajax_entity', array(
                'class'          => 'ClarolineCoreBundle:User',
                'use_controller' => true,
                'property'       => 'username',
                'repo_method'    => 'findByNameForAjax',
                'placeholder'    => $this->translator->trans('select_user', array(), 'icap_portfolio'),
                'mapped'         => false
            ));
    }

    public function getName()
    {
        return 'icap_portfolio_evaluators_form';
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
