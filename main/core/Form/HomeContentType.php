<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form;

use Claroline\CoreBundle\Entity\Content;
use Claroline\CoreBundle\Form\Field\Content as ContentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HomeContentType extends AbstractType
{
    private $type;
    private $father;

    public function __construct()
    {
        $this->name = 'content';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['id']) {
            $this->name .= $options['id'];
        }
        if ('menu' === $options['type'] && $options['father']) {
            $builder->add(
                $this->name,
                ContentType::class,
                [
                    'data' => $builder->getData(),
                    'theme_options' => [
                        'titlePlaceHolder' => 'menu_title',
                        'contentText' => false,
                        'tinymce' => false,
                    ],
                ]
            );
        } elseif ('menu' === $options['type']) {
            $builder->add(
                $this->name,
                ContentType::class,
                [
                    'data' => $builder->getData(),
                    'theme_options' => [
                        'titlePlaceHolder' => 'link_title',
                        'textPlaceHolder' => 'link_address',
                        'tinymce' => false,
                    ],
                ]
            );
        } else {
            $builder->add($this->name, ContentType::class, ['data' => $builder->getData()]);
        }
    }

    public function getName()
    {
        return 'home_content_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'id' => null,
            'type' => null,
            'father' => null,
            'translation_domain' => 'platform',
            'validation_groups' => ['registration', 'Default'],
        ]);
    }
}
