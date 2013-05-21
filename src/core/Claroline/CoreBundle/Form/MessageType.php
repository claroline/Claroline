<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Claroline\CoreBundle\Validator\Constraints\SendToUsernames;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class MessageType extends AbstractType
{
    /**
     * Constructor.
     *
     * @param string $username
     * @param string $object
     * @param boolean $isFast indicate if the message is an answer of a previous message
     * (no need to show the object nor the username).
     *
     * @throws \Exception
     */
    public function __construct($username = '', $object = '', $isFast = false)
    {
        $this->username = $username;
        $this->isFast = $isFast;
        $this->object = $object;

        if ($isFast) {
            if ($username == '' || $username == null) {
                throw new \Exception('username required');
            }
            if ($object == '' || $object == null) {
                throw new \Exception('object required');
            }
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$this->isFast) {
            $builder
                ->add(
                    'to',
                    'text',
                    array(
                        'data' =>
                        $this->username,
                        'required' => true,
                        'mapped' => false,
                        'constraints' => array(
                            new NotBlank(),
                            new SendToUsernames()
                            )
                        )
                );
        } else {
            $builder
                ->add(
                    'to',
                    'hidden',
                    array(
                        'data' => $this->username,
                        'required' => true,
                        'mapped' => false,
                        'constraints' => array(
                            new NotBlank(),
                            new SendToUsernames()
                            )
                        )
                );
        }

        $builder->add('object', 'text', array('data' => $this->object, 'required' => true))
                ->add('content', 'textarea', 
                    array('required' => true,
                        'attr' => array (
                    'class'=> 'tinymce',
                    'data-theme' => 'medium')
                    )
                );
    }

    public function getName()
    {
        return 'message_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            array(
                'translation_domain' => 'platform'
                )
        );
    }
}