<?php

namespace Claroline\CoreBundle\Finder\Template;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\Type\ChoiceType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\CoreBundle\Entity\Template\TemplateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TemplateTypeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TemplateType::class,
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['mode' => TextType::MODE_EXACT])
            ->add('type', ChoiceType::class, ['choices' => ['pdf', 'email', 'other']])
        ;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
