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
            array() :
            $this->workspaceOptions->getDetails();
        $backgroundColor = isset($details['background_color']) ? $details['background_color'] : null;
        $hideToolsMenu = isset($details['hide_tools_menu']) ? $details['hide_tools_menu'] : false;

        $builder->add(
            'hideToolsMenu',
            'checkbox',
            array(
                'required' => false,
                'mapped' => false,
                'data' => $hideToolsMenu,
                'label' => 'hide_tools_menu'
            )
        );
        $builder->add(
            'backgroundColor',
            'text',
            array(
                'required' => false,
                'mapped' => false,
                'data' => $backgroundColor,
                'label' => 'background_color'
            )
        );
    }

    public function getName()
    {
        return 'workspace_options_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}
