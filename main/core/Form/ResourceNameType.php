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

class ResourceNameType extends AbstractType
{
    private $withPublication;

    public function __construct($withPublication = false)
    {
        $this->withPublication = $withPublication;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            TextType::class,
            ['label' => 'name', 'constraints' => new NotBlank(), 'attr' => ['autofocus' => true]]
        );

        if ($this->withPublication) {
            $builder->add(
                'published',
                CheckboxType::class,
                [
                    'label' => 'publish_resource',
                    'required' => true,
                    'mapped' => false,
                    'attr' => ['checked' => 'checked'],
                ]
            );
        }
    }

    public function getName()
    {
        return 'resource_name_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'platform']);
    }
}
