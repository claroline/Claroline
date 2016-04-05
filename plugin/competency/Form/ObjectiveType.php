<?php

namespace HeVinci\CompetencyBundle\Form;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @DI\Service("hevinci_form_objective")
 * @DI\Tag("form.type")
 */
class ObjectiveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', ['label' => 'name']);
    }

    public function getName()
    {
        return 'hevinci_form_objective';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'platform',
            'data_class' => 'HeVinci\CompetencyBundle\Entity\Objective'
        ]);
    }
}

