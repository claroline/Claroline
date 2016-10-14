<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnnouncementBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AnnouncementsWidgetConfigurationType extends AbstractType
{
    private $resourceIds;

    public function __construct($resourceIds = [])
    {
        $this->resourceIds = $resourceIds;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'resource',
            'resourcePicker',
            [
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'data-is-picker-multi-select-allowed' => false,
                    'data-is-directory-selection-allowed' => false,
                    'data-type-white-list' => 'claroline_announcement_aggregate',
                ],
                'display_view_button' => false,
                'display_browse_button' => true,
                'display_download_button' => false,
                'data' => count($this->resourceIds) > 0 ? $this->resourceIds[0] : null,
            ]
        );
    }

    public function getName()
    {
        return 'announcements_widget_configuration_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'platform']);
    }
}
