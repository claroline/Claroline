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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Claroline\CoreBundle\Entity\Content;

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
        if ($this->type === 'menu' && !$this->father) {
            $builder->add(
                $this->name,
                'content',
                array(
                    'data' => $builder->getData(),
                    'theme_options' => array(
                        'titlePlaceHolder' => 'menu_title',
                        'contentText' => false,
                        'tinymce' => false,
                    ),
                )
            );
        } elseif ($this->type === 'menu') {
            $builder->add(
                $this->name,
                'content',
                array(
                    'data' => $builder->getData(),
                    'theme_options' => array(
                        'titlePlaceHolder' => 'link_title',
                        'textPlaceHolder' => 'link_address',
                        'tinymce' => false,
                    ),
                )
            );
        } else {
            $builder->add($this->name, 'content', array('data' => $builder->getData()));
        }
    }

    public function getName()
    {
        return 'home_content_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array('translation_domain' => 'platform', 'validation_groups' => array('registration', 'Default'))
        );
    }
}
