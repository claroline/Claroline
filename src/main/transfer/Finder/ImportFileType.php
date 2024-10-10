<?php

namespace Claroline\TransferBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\Type\ChoiceType;
use Claroline\AppBundle\API\Finder\Type\CreatorType;
use Claroline\AppBundle\API\Finder\Type\DateType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\RelatedEntityType;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\TransferBundle\Entity\ImportFile;
use Claroline\TransferBundle\Entity\TransferFileInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportFileType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ImportFile::class,
            'fulltext' => ['name'],
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('action', TextType::class)
            ->add('status', ChoiceType::class, [
                'choices' => [
                    TransferFileInterface::PENDING,
                    TransferFileInterface::IN_PROGRESS,
                    TransferFileInterface::ERROR,
                    TransferFileInterface::SUCCESS,
                ],
            ])
            ->add('createdAt', DateType::class)
            ->add('executedAt', DateType::class)
            ->add('workspace', RelatedEntityType::class)
            ->add('creator', CreatorType::class)
        ;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
