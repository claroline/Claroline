<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Claroline\CoreBundle\Validator\Constraints\SendToNames;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MessageType extends AbstractType
{
    private $username;
    private $object;

    /**
     * Constructor.
     *
     * @param string  $username
     * @param string  $object
     */
    public function __construct($username = null, $object = null)
    {
        $this->username = $username;
        $this->object = $object;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'to',
                'text',
                array(
                    'data' => $this->username,
                    'required' => true,
                    'mapped' => true,
                    'constraints' => array(
                        new NotBlank(),
                        new SendToNames()
                    )
                )
            )
            ->add(
                'object',
                'text',
                array('data' => $this->object, 'required' => true)
            )
            ->add(
                'content',
                'tinymce',
                array('required' => true)
            );
    }

    public function getName()
    {
        return 'message_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}
