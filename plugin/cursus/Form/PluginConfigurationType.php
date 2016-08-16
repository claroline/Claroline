<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Form;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PluginConfigurationType extends AbstractType
{
    private $configHandler;

    public function __construct(PlatformConfigurationHandler $configHandler)
    {
        $this->configHandler = $configHandler;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $attr = [];
        $attr['class'] = 'datepicker input-small';
        $attr['data-date-format'] = 'dd-mm-yyyy';
        $attr['autocomplete'] = 'off';

        $startOptions = [
            'required' => false,
            'mapped' => false,
            'format' => 'dd-MM-yyyy',
            'widget' => 'single_text',
            'attr' => $attr,
            'input' => 'datetime',
            'label' => 'default_session_start_date',
        ];
        $defaultStartDate = $this->configHandler->getParameter('cursusbundle_default_session_start_date');

        if (!empty($defaultStartDate)) {
            $startOptions['data'] = new \DateTime($defaultStartDate);
        }
        $endOptions = [
            'required' => false,
            'mapped' => false,
            'format' => 'dd-MM-yyyy',
            'widget' => 'single_text',
            'attr' => $attr,
            'input' => 'datetime',
            'label' => 'default_session_start_date',
        ];
        $defaultEndDate = $this->configHandler->getParameter('cursusbundle_default_session_end_date');

        if (!empty($defaultEndDate)) {
            $endOptions['data'] = new \DateTime($defaultEndDate);
        }

        $builder->add(
            'startDate',
            'datepicker',
            $startOptions
        );
        $builder->add(
            'endDate',
            'datepicker',
            $endOptions
        );
        $builder->add(
            'content',
            'content',
            [
                'data' => $builder->getData(),
                'theme_options' => ['contentTitle' => true],
            ]
        );
    }

    public function getName()
    {
        return 'cursus_plugin_configuration_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'cursus']);
    }
}
