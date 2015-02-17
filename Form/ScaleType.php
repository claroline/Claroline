<?php

namespace HeVinci\CompetencyBundle\Form;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @DI\Service("hevinci_form_scale")
 * @DI\Tag("form.type")
 */
class ScaleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', ['label' => 'name'])
            ->add('levels', 'scale_levels');
    }

    public function getName()
    {
        return 'hevinci_form_scale';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'platform',
            'data_class' => 'HeVinci\CompetencyBundle\Entity\Scale'
        ]);
    }
}
