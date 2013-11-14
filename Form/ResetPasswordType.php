<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'plainPassword',
            'repeated',
            array(
                'type' => 'password',
                'required' => true
            )
        );
    }

    public function getName()
    {
        return 'reset_pwd_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Claroline\CoreBundle\Entity\User',
            'translation_domain' => 'platform'
        ));
    }
}
