<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form;

use Claroline\CoreBundle\Entity\Workspace\WorkspaceOptions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WorkspaceOptionsType extends AbstractType
{
    private $workspaceOptions;

    public function __construct(WorkspaceOptions $workspaceOptions = null)
    {
        $this->workspaceOptions = $workspaceOptions;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $details = is_null($this->workspaceOptions) ?
            [] :
            $this->workspaceOptions->getDetails();
        $backgroundColor = isset($details['background_color']) ?
            $details['background_color'] :
            null;
        $hideToolsMenu = isset($details['hide_tools_menu']) ?
            $details['hide_tools_menu'] :
            false;
        $hideBreadcrumb = isset($details['hide_breadcrumb']) ?
            $details['hide_breadcrumb'] :
            false;
        $useDefaultResource = isset($details['use_workspace_opening_resource']) ?
            $details['use_workspace_opening_resource'] :
            false;
        $defaultResourceId = isset($details['workspace_opening_resource']) ?
            $details['workspace_opening_resource'] :
            null;

        $builder->add(
            'hideToolsMenu',
            'checkbox',
            [
                'required' => false,
                'mapped' => false,
                'data' => $hideToolsMenu,
                'label' => 'hide_tools_menu',
            ]
        );
        $builder->add(
            'hideBreadcrumb',
            'checkbox',
            [
                'required' => false,
                'mapped' => false,
                'data' => $hideBreadcrumb,
                'label' => 'hide_breadcrumb',
            ]
        );
        $builder->add(
            'backgroundColor',
            'text',
            [
                'required' => false,
                'mapped' => false,
                'data' => $backgroundColor,
                'label' => 'background_color',
            ]
        );
        $builder->add(
            'useWorkspaceOpeningResource',
            'checkbox',
            [
                'required' => false,
                'mapped' => false,
                'data' => $useDefaultResource,
                'label' => 'open_resource_when_opening_ws',
            ]
        );
        $builder->add(
            'workspaceOpeningResource',
            'resourcePicker',
            [
                'required' => false,
                'mapped' => false,
                'data' => $defaultResourceId,
                'label' => 'resource_to_open',
            ]
        );
    }

    public function getName()
    {
        return 'workspace_options_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'platform']);
    }
}
