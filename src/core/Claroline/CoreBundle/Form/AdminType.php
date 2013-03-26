<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
/**
 * Form used to collect minimal information on the administrator in the plaform
 * installation process.
 */
class AdminType extends BaseProfileType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('mail', 'email', array('required' => false));
    }

    public function getName()
    {
        return 'admin_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver

        ->setDefaults(
            array(
                'class' => 'Claroline\CoreBundle\Entity\User',
                'translation_domain' => 'platform'
                )
        );
    }
}
