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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HomeTemplateType extends AbstractType
{
    private $templates = [];

    public function __construct($templatesDir)
    {
        $contents = is_dir($templatesDir) ? scandir($templatesDir) : [];

        foreach ($contents as $content) {
            if (!is_dir($content)) {
                $this->templates[$content] = $content;
            }
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'template',
            ChoiceType::class,
            [
                'required' => false,
                'choices' => $this->templates,
                'label' => 'template',
            ]
        );
    }

    public function getName()
    {
        return 'home_template_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'platform']);
    }
}
