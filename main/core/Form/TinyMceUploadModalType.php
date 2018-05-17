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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class TinyMceUploadModalType extends AbstractType
{
    private $uncompress;
    private $destinations;

    public function __construct($destinations = [], $uncompress = false)
    {
        $this->uncompress = $uncompress;
        $this->destinations = [];

        foreach ($destinations as $destination) {
            $nodeId = $destination->getResourceNode()->getId();
            $this->destinations[$nodeId] = $destination->getResourceNode()->getPathForDisplay();
        }

        $this->destinations['others'] = 'others';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', HiddenType::class, ['data' => 'tmpname']);
        $builder->add(
            FileType::class,
            HiddenType::class,
            [
                'label' => FileType::class,
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new NotBlank(),
                    new File(),
                ],
           ]
        );
        if (count($this->destinations) > 1) {
            $builder->add(
                'destination',
                ChoiceType::class,
                [
                    'label' => 'destination',
                    'mapped' => false,
                    'choices' => $this->destinations,
                ]
            );
        }
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
                'label' => 'published',
                'required' => true,
                'mapped' => false,
                'attr' => ['checked' => 'checked'],
           ]
        );
    }

    public function getName()
    {
        return 'file_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
        ->setDefaults(
            [
                'translation_domain' => 'platform',
                ]
        );
    }
}
