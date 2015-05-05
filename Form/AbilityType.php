<?php

namespace HeVinci\CompetencyBundle\Form;

use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Repository\LevelRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @DI\Service("hevinci_form_ability")
 * @DI\Tag("form.type")
 */
class AbilityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options['data'] = isset($options['data']) ? $options['data'] : new Ability();

        if (!$options['competency'] instanceof Competency) {
            throw new \LogicException(
                'Missing "competency" option: unable to determine a scale in ability form'
            );
        }

        $builder
            ->add('name', 'textarea', [
                'label' => 'description',
                'attr' => ['class' => 'form-control']
            ])
            ->add('level', 'entity', [
                'label' => 'level_',
                'translation_domain' => 'competency',
                'class' => 'HeVinciCompetencyBundle:Level',
                'choice_label' => 'name',
                'query_builder' => function (LevelRepository $repo) use ($options) {
                    return $repo->getFindByCompetencyBuilder($options['competency']);
                },
            ])
            ->add('minActivityCount', 'integer', [
                'label' => 'ability.min_activity_count',
                'translation_domain' => 'competency',
                'attr' => ['min' => 0, 'max' => 1000],
                'data' => $options['data']->getMinActivityCount()
            ]);
    }

    public function getName()
    {
        return 'hevinci_form_ability';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'platform',
            'data_class' => 'HeVinci\CompetencyBundle\Entity\Ability',
            'competency' => null
        ]);
    }
}
