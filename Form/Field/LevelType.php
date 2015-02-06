<?php

namespace HeVinci\CompetencyBundle\Form\Field;

use Doctrine\Common\Persistence\ObjectManager;
use HeVinci\CompetencyBundle\Form\DataTransformer\LevelTransformer;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @DI\Service
 * @DI\Tag("form.type", attributes={"alias"="scale_levels"})
 */
class LevelType extends AbstractType
{
    private $om;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new LevelTransformer($this->om));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'label' => 'levels',
            'translation_domain' => 'competency',
            'attr' => [
                'class' => 'form-control',
                'rows' => 6,
                'placeholder' => 'info.scale_levels'
            ]
        ]);
    }

    public function getParent()
    {
        return 'textarea';
    }

    public function getName()
    {
        return 'scale_levels';
    }
}
