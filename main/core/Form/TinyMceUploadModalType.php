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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $destinations = [];

        foreach ($options['destinations'] as $destination) {
            $nodeId = $destination->getResourceNode()->getId();
            $destinations[$nodeId] = $destination->getResourceNode()->getPathForDisplay();
        }

        $destinations['others'] = 'others';

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

        if (count($destinations) > 1) {
            $builder->add(
                'destination',
                ChoiceType::class,
                [
                    'label' => 'destination',
                    'mapped' => false,
                    'choices' => $destinations,
                ]
            );
        }

        if ($options['uncompress']) {
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'platform',
                'destinations' => [],
                'uncompress' => false,
            ]
        );
    }
}
