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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

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
        $builder->add('name', HiddenType::class, ['data' => 'tmpname']);
        $builder->add(
            'file',
            self::class,
            [
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new NotBlank(),
                    new File(),
                ],
                'label' => self::class,
           ]
        );
        if ($this->uncompress) {
            $builder->add(
                'uncompress',
                CheckboxType::class,
                [
                    'label' => 'uncompress_file',
                    'mapped' => false,
                    'required' => false,
                ]
            );
        }
        $builder->add(
            'published',
            CheckboxType::class,
            [
                'required' => true,
                'mapped' => false,
                'attr' => ['checked' => 'checked'],
                'label' => 'publish_resource',
           ]
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $default = ['translation_domain' => 'platform'];
        if ($this->forApi) {
            $default['csrf_protection'] = false;
        }
        $resolver->setDefaults($default);
    }
}
