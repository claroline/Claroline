<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TagBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WorkspaceTagType extends AbstractType
{
    private $tags;

    public function __construct($tags = [])
    {
        $this->tags = $tags;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
        return 'workspace_tag_widget_configuration_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'platform']);
    }
}
