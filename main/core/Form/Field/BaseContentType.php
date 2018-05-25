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

use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use Symfony\Component\Form\Extension\Core\Type\BaseType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @Service("claroline.form.base_content")
 * @Tag("form.type")
 */
class BaseContentType extends BaseType
{
    public function getName()
    {
        return 'base_content';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $title = '';
        $content = '';

        if (is_array($translatedContent = $builder->getData())) {
            if (isset($translatedContent['title'])) {
                $title = $translatedContent['title'];
            }
            if (isset($translatedContent['content'])) {
                $content = $translatedContent['content'];
            }
        }

        $builder->add('title', TextType::class, ['data' => $title]);
        if (isset($options['attr']['tinymce']) && !$options['attr']['tinymce']) {
            $builder->add(
                'content',
                TextareaType::class,
                [
                    'attr' => ['class' => 'form-control', 'rows' => '3'],
                    'mapped' => false,
                    'data' => $content,
                ]
            );
        } else {
            $builder->add('content', TinymceType::class, ['data' => $content]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['required' => false, 'mapped' => false, 'attr' => []]);
    }
}
