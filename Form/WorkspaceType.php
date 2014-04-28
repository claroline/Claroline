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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Claroline\CoreBundle\Validator\Constraints\WorkspaceUniqueCode;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WorkspaceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add(
                'code',
                'text',
                array('constraints' => array(new WorkspaceUniqueCode()))
            )->add(
                'description',
                isset($options['theme_options']['tinymce']) && !$options['theme_options']['tinymce'] ?
                    'textarea' :
                    'tinymce',
                array('required' => false)
            )->add(
                'template',
                'entity',
                array(
                    'class' => 'ClarolineCoreBundle:Workspace\Template',
                    'property' => 'name'
                )
            )->add('displayable', 'checkbox', array('required' => false))
            ->add('selfRegistration', 'checkbox', array('required' => false))
            ->add('selfUnregistration', 'checkbox', array('required' => false));
    }

    public function getName()
    {
        return 'workspace_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}
