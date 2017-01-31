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
            ['label' => 'client_name']
        );
        $builder->add(
            'clientIp',
            'text',
            ['label' => 'client_ip']
        );
        $builder->add(
            'token',
            'text',
            ['label' => 'token']
        );
    }

    public function getName()
    {
        return 'security_token_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            ['translation_domain' => 'platform']
        );
    }
}
