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

class ChatUsersCreationType extends AbstractType
{
    private $blackList;

    public function __construct(array $blackList = array())
    {
        $this->blackList = $blackList;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'users',
            'userpicker',
            array(
                'multiple' => true,
                'picker_name' => 'chat_users_selections',
                'picker_title' => 'chat_users_selection_for_generation',
                'blacklist' => $this->blackList,
                'mapped' => false
            )
        );
    }

    public function getName()
    {
        return 'chat_users_creation_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'chat'));
    }
}
