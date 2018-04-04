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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class HomeContentType extends AbstractType
{
    private $name = 'content';
    private $type;
    private $father;

    public function __construct($id, $type = null, $father = null)
    {
        if ($id) {
            $this->name .= $id;
        }

        $this->type = $type;
        $this->father = $father;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ('menu' === $this->type && !$this->father) {
            $builder->add(
                $this->name,
                'content',
                [
                    'data' => $builder->getData(),
                    'theme_options' => [
                        'titlePlaceHolder' => 'menu_title',
                        'contentText' => false,
                        'tinymce' => false,
                    ],
                ]
            );
        } elseif ('menu' === $this->type) {
            $builder->add(
                $this->name,
                'content',
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
            $builder->add($this->name, 'content', ['data' => $builder->getData()]);
        }
    }

    public function getName()
    {
        return 'home_content_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            ['translation_domain' => 'platform', 'validation_groups' => ['registration', 'Default']]
        );
    }
}
