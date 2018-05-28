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

//FORM TODO
class DirectoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            TextType::class,
            [
                'constraints' => new NotBlank(),
                'label' => 'name',
                'attr' => ['autofocus' => true],
            ]
        );
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'platform']);
    }
}
