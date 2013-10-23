<?php

namespace Claroline\CoreBundle\Form;

use Claroline\CoreBundle\Repository\WidgetRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class WidgetInstanceType extends AbstractType
{
    private $isDesktop;

    public function __construct($isDesktop = true)
    {
        $this->isDesktop = $isDesktop;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isDesktop = $this->isDesktop;
        $builder->add('name', 'text', array('constraints' => new NotBlank()));
        $builder->add(
            'widget',
            'entity',
            array(
                'class' => 'Claroline\CoreBundle\Entity\Widget\Widget',
                'expanded' => false,
                'multiple' => false,
                'query_builder' => function (WidgetRepository $widgetRepo) use ($isDesktop) {
                    if ($isDesktop) {
                        return $widgetRepo->createQueryBuilder('w')
                            ->where('w.isDisplayableInDesktop = true');
                    } else {
                        return $widgetRepo->createQueryBuilder('w')
                            ->where('w.isDisplayableInWorkspace = true');
                    }
                }
            )
        );
    }

    public function getName()
    {
        return 'widget_instance_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'translation_domain' => 'widget'
            )
        );
    }
}