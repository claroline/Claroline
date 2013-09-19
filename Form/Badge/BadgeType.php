<?php

namespace Claroline\CoreBundle\Form\Badge;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @DI\Service("claroline.form.badge")
 */
class BadgeType extends AbstractType
{
    /** @var \Claroline\CoreBundle\Form\Badge\BadgeRuleType */
    private $badgeRuleType;

    /**
     * @DI\InjectParams({
     *     "badgeRuleType" = @DI\Inject("claroline.form.badgeRule")
     * })
     */
    public function __construct(BadgeRuleType $badgeRuleType)
    {
        $this->badgeRuleType = $badgeRuleType;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('frTranslation', new BadgeTranslationType())
            ->add('enTranslation', new BadgeTranslationType())
            ->add('version', 'integer')
            ->add('automatic_award', 'checkbox', array(
                'required' => false
            ))
            ->add('file', 'file', array(
                'label'    => 'badge_form_image'
            ))
            ->add('expired_at', 'datepicker', array(
                'read_only' => true,
                'component' => true,
                'autoclose' => true,
                'language'  => $options['language'],
                'format'    => $options['date_format']
            ))
            ->add('badgeRules', 'collection', array(
                'type'          => $this->badgeRuleType,
                'by_reference'  => false,
                'attr'          => array('class' => 'rule-collections'),
                'theme_options' => array('label_width' => 'col-md-3'),
                'prototype'     => true,
                'allow_add'     => true,
                'allow_delete'  => true
             ));
    }

    public function getName()
    {
        return 'badge_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            array(
                'data_class'         => 'Claroline\CoreBundle\Entity\Badge\Badge',
                'translation_domain' => 'badge',
                'language'           => 'en',
                'date_format'        => DateType::HTML5_FORMAT
            )
        );
    }
}
