<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Field;

use Claroline\CoreBundle\Manager\LocaleManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\DiExtraBundle\Annotation\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @DI\Service("claroline.form.datepicker")
 * @Tag("form.type")
 */
class DatePickerType extends AbstractType
{
    /**
     * @DI\InjectParams({
     *     "localeManager" = @DI\Inject("claroline.manager.locale_manager"),
     *     "container"     = @DI\Inject("service_container")
     * })
     */
    public function __construct(LocaleManager $localeManager, $container)
    {
        $this->localeManager = $localeManager;
        $this->container = $container;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['component'] = $options['component'];
        $view->vars['options'] = [
            'autoclose' => $options['autoclose'],
            'language' => $options['language'],
        ];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $lang = ('cli' === php_sapi_name()) ? 'en' : $this->localeManager->getUserLocale($this->container->get('request_stack')->getMasterRequest());
        $resolver->setDefaults(
            [
                'input' => 'datetime',
                'widget' => 'single_text',
                'component' => false,
                'autoclose' => false,
                'language' => $lang,
            ]
        );
    }

    public function getParent()
    {
        return 'datetime';
    }

    public function getName()
    {
        return 'datepicker';
    }
}
