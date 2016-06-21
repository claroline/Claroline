<?php

namespace Icap\BadgeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("icap_badge.form.badge.award")
 */
class BadgeAwardType extends AbstractType
{
    /** @var \Symfony\Component\Routing\RouterInterface */
    private $router;

    /** @var \Symfony\Component\Translation\TranslatorInterface */
    private $translator;

    /**
     * @DI\InjectParams({
     *     "router"     = @DI\Inject("router"),
     *     "translator" = @DI\Inject("translator")
     * })
     */
    public function __construct(RouterInterface $router, TranslatorInterface $translator)
    {
        $this->router = $router;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('group', 'zenstruck_ajax_entity', array(
                'placeholder' => $this->translator->trans('badge_award_form_group_choose', array(), 'icap_badge'),
                'class' => 'ClarolineCoreBundle:Group',
                'use_controller' => true,
                'property' => 'name',
                'repo_method' => 'findByNameForAjax',
            ))
            ->add('user', 'zenstruck_ajax_entity', array(
                'placeholder' => $this->translator->trans('badge_award_form_user_choose', array(), 'icap_badge'),
                'class' => 'ClarolineCoreBundle:User',
                'use_controller' => true,
                'property' => 'username',
                'repo_method' => 'findByNameForAjax',
            ))
            ->add('comment', 'tinymce', array(
                'required' => false,
            ));
    }

    public function getName()
    {
        return 'badge_award_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'icap_badge'));
    }
}
