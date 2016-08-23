<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ChatBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChatUserEditionType extends AbstractType
{
    private $color;

    public function __construct($color)
    {
        $this->color = $color;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'color',
            'text',
            [
                'required' => false,
                'mapped' => false,
                'data' => $this->color,
                'label' => 'color',
                'translation_domain' => 'platform',
            ]
        );
    }

    public function getName()
    {
        return 'chat_user_edition_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'chat']);
    }
}
