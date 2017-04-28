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

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Widget\ResourcesWidgetConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ResourcesWidgetConfigurationType extends AbstractType
{
    private $mode;
    private $directoriesIds;
    private $rootDir;
    private $tags;
    private $translator;
    private $workspace;

    public function __construct(
        TranslatorInterface $translator,
        $mode = ResourcesWidgetConfig::DIRECTORY_MODE,
        $directoriesIds = [],
        $tags = [],
        ResourceNode $rootDir = null,
        $workspace = null
    ) {
        $this->mode = $mode;
        $this->directoriesIds = $directoriesIds;
        $this->tags = $tags;
        $this->translator = $translator;
        $this->rootDir = $rootDir;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'mode',
            'choice',
            [
                'mapped' => false,
                'multiple' => false,
                'choices' => [
                    ResourcesWidgetConfig::DIRECTORY_MODE => $this->translator->trans('directory', [], 'platform'),
                    ResourcesWidgetConfig::TAG_MODE => $this->translator->trans('tag', [], 'platform'),
                ],
                'data' => $this->mode,
                'label' => 'mode',
            ]
        );
        $builder->add(
            'resource',
            'resourcePicker',
            [
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'data-is-picker-multi-select-allowed' => false,
                    'data-is-directory-selection-allowed' => true,
                    'data-allow-root-selection' => true,
                    'data-type-white-list' => 'directory',
//                    'data-workspace-id' => !is_null($this->workspace) ? $this->workspace->getId() : null,
//                    'data-is-workspace' => !is_null($this->workspace),
//                    'data-picker-directory-id' => !is_null($this->rootDir) ? $this->rootDir->getId() : 0,
//                    'data-directory-id' => !is_null($this->rootDir) ? $this->rootDir->getId() : 0,
//                    'data-current-directory-id' => !is_null($this->rootDir) ? $this->rootDir->getId() : 0,
//                    'data-pre-fetched-directory' => !is_null($this->rootDir) ? $this->rootDir->getId() : 0,
                ],
                'display_view_button' => false,
                'display_browse_button' => true,
                'display_download_button' => false,
                'data' => count($this->directoriesIds) > 0 ? $this->directoriesIds[0] : null,
            ]
        );
        $builder->add(
            'tags',
            'text',
            [
                'mapped' => false,
                'required' => false,
                'label' => 'tag',
                'data' => count($this->tags) > 0 ? implode(',', $this->tags) : null,
            ]
        );
    }

    public function getName()
    {
        return 'resources_widget_configuration_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'platform']);
    }
}
