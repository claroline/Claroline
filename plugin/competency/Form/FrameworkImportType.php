<?php

namespace HeVinci\CompetencyBundle\Form;

use HeVinci\CompetencyBundle\Validator\ImportableFramework;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @DI\Service("hevinci_form_import_framework")
 * @DI\Tag("form.type")
 */
class FrameworkImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', 'file', [
            'label' => 'file',
            'constraints' => [
                new NotBlank(),
                new ImportableFramework(),
            ],
        ]);
    }

    public function getName()
    {
        return 'hevinci_form_import_framework';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'platform']);
    }
}
