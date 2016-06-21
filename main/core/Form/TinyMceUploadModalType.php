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
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TinyMceUploadModalType extends AbstractType
{
    private $uncompress;
    private $destinations;

    public function __construct($destinations = array(), $uncompress = false)
    {
        $this->uncompress = $uncompress;
        $this->destinations = array();

        foreach ($destinations as $destination) {
            $nodeId = $destination->getResourceNode()->getId();
            $this->destinations[$nodeId] = $destination->getResourceNode()->getPathForDisplay();
        }

        $this->destinations['others'] = 'others';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'hidden', array('data' => 'tmpname'));
        $builder->add(
            'file',
            'hidden',
            array(
                'label' => 'file',
                'required' => true,
                'mapped' => false,
                'constraints' => array(
                    new NotBlank(),
                    new File(),
                ),
           )
        );
        if (count($this->destinations) > 1) {
            $builder->add(
                'destination',
                'choice',
                array(
                    'label' => 'destination',
                    'mapped' => false,
                    'choices' => $this->destinations,
                )
            );
        }
        if ($this->uncompress) {
            $builder->add(
                'uncompress',
                'checkbox',
                array(
                    'label' => 'uncompress_file',
                    'mapped' => false,
                    'required' => false,
                )
            );
        }
        $builder->add(
            'published',
            'checkbox',
            array(
                'label' => 'published',
                'required' => true,
                'mapped' => false,
                'attr' => array('checked' => 'checked'),
           )
        );
    }

    public function getName()
    {
        return 'file_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            array(
                'translation_domain' => 'platform',
                )
        );
    }
}
