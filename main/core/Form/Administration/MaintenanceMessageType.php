<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Administration;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MaintenanceMessageType extends AbstractType
{
    private $message;

    public function __construct($message = '')
    {
        $this->message = $message;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'message',
            'tinymce',
            array(
                'required' => false,
                'mapped' => false,
                'data' => $this->message,
            )
        );
    }

    public function getName()
    {
        return 'maintenance_message_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}
