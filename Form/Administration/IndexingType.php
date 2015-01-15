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
use Symfony\Component\Validator\Constraints\Regex;

class IndexingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'google_meta_tag',
            'text',
            array(
                'label' => 'Google HTML tag validation',
                'constraints' => array(
                   new Regex(array(
                       'pattern' => "/^\<meta name=\x22google-site-verification\x22 content=\x22([\w]){1,43}\x22( \/)?\>$/",
                       'message' => 'google_meta_tag_error'
                   ))
                ),
            )
        );
    }

    public function getName()
    {
        return 'indexing_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}
