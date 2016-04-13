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

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TextType extends AbstractType
{
    private $formName;

    public function __construct($formName = null)
    {
        $this->formName = $formName;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array('label' => 'name', 'constraints' => new NotBlank()));
        $builder->add('text', 'tinymce', array('label' => 'text'));
        $builder->add(
            'published',
            'checkbox',
            array(
                'label' => 'publish_resource',
                'required' => true,
                'mapped' => false,
                'attr' => array('checked' => 'checked'),
           )
        );
    }

    public function getName()
    {
        return $this->formName;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'class' => 'Claroline\CoreBundle\Entity\Resource\Text',
                'translation_domain' => 'platform',
            )
        );
    }
}
