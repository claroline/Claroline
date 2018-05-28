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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

//TODO FORM
class ActivityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, ['label' => 'name', 'constraints' => new NotBlank(), 'attr' => ['autofocus' => true]])
            ->add('description', 'tinymce', ['required' => false, 'label' => 'description'])
            ->add(
                'primaryResource',
                'resourcePicker',
                [
                    'required' => false,
                    'attr' => [
                        'data-blacklist' => 'activity,directory',
                    ],
                ]
            )->add(
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

    public function getName()
    {
        return 'activity_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'platform']);
    }
}
