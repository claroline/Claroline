<?php

namespace Icap\BadgeBundle\Form\Field;

use Icap\BadgeBundle\Form\DataTransformer\BadgePickerTransformer;
use Icap\BadgeBundle\Manager\BadgeManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @DI\Service("icap_badge.form.badgepicker")
 * @DI\FormType(alias = "badgepicker")
 */
class BadgePickerType extends AbstractType
{
    /**
     * @var BadgePickerTransformer
     */
    private $badgePickerTransformer;

    /**
     * @DI\InjectParams({
     *     "badgePickerTransformer" = @DI\Inject("icap_badge.transformer.badge_picker")
     * })
     */
    public function __construct(BadgePickerTransformer $badgePickerTransformer)
    {
        $this->badgePickerTransformer = $badgePickerTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->badgePickerTransformer);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['multiple'] = $options['multiple'];
        $view->vars['mode'] = $options['mode'];
        $view->vars['workspace'] = $options['workspace'];
        $view->vars['blacklist'] = $options['blacklist'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'multiple' => false,
                'mode' => BadgeManager::BADGE_PICKER_DEFAULT_MODE,
                'workspace' => null,
                'blacklist' => array(),
            )
        );
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'badgepicker';
    }
}
