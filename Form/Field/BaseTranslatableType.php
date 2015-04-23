<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Field;

use Symfony\Component\Form\Extension\Core\Type\BaseType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\FormType;

/**
 * @Service("claroline.form.base_translatable")
 * @FormType(alias = "base_translatable")
 */
class BaseTranslatableType extends BaseType
{
    public function getName()
    {
        return 'base_translatable';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translated = '';

        if (is_array($translatedContent = $builder->getData())) {
            $translated = array_shift($translatedContent);
        }

        $builder->add('title', 'text', array('data' => $translated));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('required' => false, 'mapped' => false));
    }
}
