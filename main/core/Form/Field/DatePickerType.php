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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Manager\LocaleManager;

/**
 * @DI\Service("claroline.form.datepicker")
 * @DI\FormType(alias = "datepicker")
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
        $view->vars['options'] = array(
            'autoclose' => $options['autoclose'],
            'language' => $options['language'],
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $lang = (php_sapi_name() === 'cli') ? 'en' : $this->localeManager->getUserLocale($this->container->get('request'));
        $resolver->setDefaults(
            array(
                'input' => 'datetime',
                'widget' => 'single_text',
                'component' => false,
                'autoclose' => false,
                'language' => $lang,
            )
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
