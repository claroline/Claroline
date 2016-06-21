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

class SecurityTokenType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'clientName',
            'text',
            array('label' => 'client_name')
        );
        $builder->add(
            'clientIp',
            'text',
            array('label' => 'client_text')
        );
        $builder->add(
            'token',
            'text',
            array('label' => 'token')
        );
    }

    public function getName()
    {
        return 'security_token_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array('translation_domain' => 'platform')
        );
    }
}
