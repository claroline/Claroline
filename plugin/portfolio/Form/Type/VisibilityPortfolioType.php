<?php

namespace Icap\PortfolioBundle\Form\Type;

use Icap\PortfolioBundle\Entity\Portfolio;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\FormType
 */
class VisibilityPortfolioType extends AbstractType
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
            ->add('visibility', 'choice', array(
                'choices'       => Portfolio::getVisibilityLabels(),
                'required'      => true,
                'label'         => 'visibility',
                'theme_options' => array('label_width' => 'col-md-2'),
            ))
            ->add('portfolio_users', 'collection', array(
                'type'          => 'icap_portfolio_visible_user_form',
                'by_reference'  => false,
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
            ))
            ->add('portfolio_groups', 'collection', array(
                'type'          => 'icap_portfolio_visible_group_form',
                'by_reference'  => false,
                'theme_options' => array('label_width' => 'col-md-12'),
                'prototype'     => true,
                'allow_add'     => true,
                'allow_delete'  => true
            ))
            ->add('search_group', 'zenstruck_ajax_entity', array(
                'class'          => 'ClarolineCoreBundle:Group',
                'use_controller' => true,
                'property'       => 'name',
                'repo_method'    => 'findByNameForAjax',
                'placeholder'    => $this->translator->trans('select_group', array(), 'icap_portfolio'),
                'mapped'         => false
            ))
            ->add('portfolio_teams', 'collection', array(
                'type'          => 'icap_portfolio_visible_team_form',
                'by_reference'  => false,
                'theme_options' => array('label_width' => 'col-md-12'),
                'prototype'     => true,
                'allow_add'     => true,
                'allow_delete'  => true
            ))
            ->add('search_team', 'zenstruck_ajax_entity', array(
                'class'          => 'ClarolineTeamBundle:Team',
                'use_controller' => true,
                'property'       => 'name',
                'placeholder'    => $this->translator->trans('select_team', array(), 'icap_portfolio'),
                'mapped'         => false
            ));
    }

    public function getName()
    {
        return 'icap_portfolio_visibility_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'Icap\PortfolioBundle\Entity\Portfolio',
                'translation_domain' => 'icap_portfolio'
            )
        );
    }
}
