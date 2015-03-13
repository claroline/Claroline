<?php

namespace HeVinci\CompetencyBundle\Form;

use Claroline\CoreBundle\Persistence\ObjectManager;
use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Form\DataTransformer\AbilityImportTransformer;
use HeVinci\CompetencyBundle\Repository\LevelRepository;
use HeVinci\CompetencyBundle\Validator\ExistingAbility;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @DI\Service("hevinci_form_ability_import")
 * @DI\Tag("form.type")
 */
class AbilityImportType extends AbstractType
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
        $options['data'] = isset($options['data']) ? $options['data'] : new Ability();

        if (!$options['competency'] instanceof Competency) {
            throw new \LogicException(
                'Missing "competency" option: unable to determine a scale in ability import form'
            );
        }

        $builder
            ->add('ability', 'textarea', [
                'label' => 'ability_',
                'constraints' => [new NotBlank(), new ExistingAbility()],
                'attr' => [
                    'placeholder' => 'info.ability_search',
                    'class' => 'ability-search form-control',
                    'rows' => 3
                ]
            ])
            ->add('level', 'entity', [
                'label' => 'level',
                'class' => 'HeVinciCompetencyBundle:Level',
                'property' => 'name',
                'query_builder' => function (LevelRepository $repo) use ($options) {
                    return $repo->getFindByCompetencyBuilder($options['competency']);
                },
            ])
            ->addModelTransformer(new AbilityImportTransformer($this->om));
    }

    public function getName()
    {
        return 'hevinci_form_ability_import';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'competency',
            'competency' => null
        ]);
    }
}
