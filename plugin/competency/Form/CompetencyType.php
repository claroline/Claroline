<?php

namespace HeVinci\CompetencyBundle\Form;

use HeVinci\CompetencyBundle\Validator\UniqueCompetency;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @DI\Service("hevinci_form_competency")
 * @DI\Tag("form.type")
 */
class CompetencyType extends AbstractType
{
    private $uniqueNameConstraint;

    public function __construct()
    {
        $this->uniqueNameConstraint = new UniqueCompetency();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->uniqueNameConstraint->parentCompetency = $options['parent_competency'];
        $builder->add('name', 'textarea', [
            'label' => 'description',
            'attr' => ['class' => 'form-control'],
        ]);
    }

    public function getName()
    {
        return 'hevinci_form_competency';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'platform',
            'data_class' => 'HeVinci\CompetencyBundle\Entity\Competency',
            'parent_competency' => null,
            'constraints' => [$this->uniqueNameConstraint],
        ]);
    }
}
