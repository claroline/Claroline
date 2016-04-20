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

class FileType extends AbstractType
{
    private $uncompress;
    private $forApi;

    public function __construct($uncompress = false)
    {
        $this->uncompress = $uncompress;
        $this->forApi = false;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'hidden', array('data' => 'tmpname'));
        $builder->add(
            'file',
            'file',
            array(
                'required' => true,
                'mapped' => false,
                'constraints' => array(
                    new NotBlank(),
                    new File(),
                ),
                'label' => 'file',
           )
        );
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
                'required' => true,
                'mapped' => false,
                'attr' => array('checked' => 'checked'),
                'label' => 'publish_resource',
           )
        );
    }

    public function enableApi()
    {
        $this->forApi = true;
    }

    public function getName()
    {
        return 'file_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $default = array('translation_domain' => 'platform');
        if ($this->forApi) {
            $default['csrf_protection'] = false;
        }
        $resolver->setDefaults($default);
    }
}
