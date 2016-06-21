<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MessageBundle\Form;

use Claroline\MessageBundle\Validator\Constraints\SendToNames;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class MessageType extends AbstractType
{
    private $username;
    private $object;

    /**
     * Constructor.
     *
     * @param string $username
     * @param string $object
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
                        new SendToNames(),
                    ),
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
