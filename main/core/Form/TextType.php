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
use Symfony\Component\Form\Extension\Core\Type\TextType as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class TextType extends AbstractType
{
    //FORM TODO
    private $formName;

    public function __construct($formName = null)
    {
        $this->formName = $formName;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', Type::class, ['label' => 'name', 'constraints' => new NotBlank(), 'attr' => ['autofocus' => true]]);
        $builder->add('text', 'tinymce', ['label' => self::class]);
        $builder->add(
            'published',
            CheckboxType::class,
            [
                'label' => 'publish_resource',
                'required' => true,
                'mapped' => false,
                'attr' => ['checked' => 'checked'],
           ]
        );
    }

    public function getName()
    {
        return $this->formName;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Claroline\CoreBundle\Entity\Resource\Text',
                'translation_domain' => 'platform',
            ]
        );
    }
}
