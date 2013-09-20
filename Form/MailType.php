<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Email;

class MailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'receiver',
                'text',
                array(
                    'required' => true,
                    'constraints' => new Email()
                )
            )
            ->add('object', 'text')
            ->add(
                'content',
                'textarea',
                array(
                    'attr' => array(
                        'class' => 'tinymce',
                        'data-theme' => 'simple'
                    )
                )
            );
    }

    public function getName()
    {
        return 'mail_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}
