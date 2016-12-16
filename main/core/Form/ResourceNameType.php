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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
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
            'text',
            ['label' => 'name', 'constraints' => new NotBlank(), 'attr' => ['autofocus' => true]]
        );

        if ($this->withPublication) {
            $builder->add(
                'published',
                'checkbox',
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

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'platform']);
    }
}
