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

use JMS\DiExtraBundle\Annotation as DI;
use JMS\DiExtraBundle\Annotation\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @DI\Service("claroline.form.simpleautocomplete")
 * @Tag("form.type")
 */
class SimpleAutoCompleteType extends AbstractType
{
    public function getParent()
    {
        return TextType::class;
    }

    public function getName()
    {
        return 'simpleautocomplete';
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['entity_reference'] = $options['entity_reference'];
        $view->vars['with_vendors'] = $options['with_vendors'];
        $view->vars['format'] = $options['format'];
        $view->vars['extraDatas'] = $options['extraDatas'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'entity_reference' => null,
                'with_vendors' => true,
                'format' => 'json',
                'extraDatas' => [],
            ]
        );
    }
}
