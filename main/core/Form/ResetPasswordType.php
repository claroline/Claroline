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

class ResetPasswordType extends AbstractType
{
    private $resetPwd = null;

    public function __construct($resetPwd = false)
    {
        $this->resetPwd = $resetPwd;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->resetPwd) {
            $builder->add('password', 'password');
        }
        $builder->add(
            'plainPassword',
            'repeated',
            array(
                'type' => 'password',
                'invalid_message' => 'password_mismatch',
                'first_options' => array('label' => 'new_password'),
                'second_options' => array('label' => 'repeat_password'),
            )
        );
    }

    public function getName()
    {
        return 'reset_pwd_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'translation_domain' => 'platform',
            )
        );
    }
}
