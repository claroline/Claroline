<?php

namespace UJM\ExoBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Claroline\AppBundle\API\Finder\Type\ChoiceType;
use Claroline\AppBundle\API\Finder\Type\CreatorType;
use Claroline\AppBundle\API\Finder\Type\DateType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\TagType;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\CoreBundle\Security\PlatformRoles;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Entity\Item\Shared;
use UJM\ExoBundle\Library\Item\ItemDefinitionsCollection;

class ItemType extends AbstractType
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ItemDefinitionsCollection $definitionsCollection
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Item::class,
            'fulltext' => ['title', 'content', 'description'],
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $itemTypes = $this->definitionsCollection->getSupportedTypes();
        $itemTypes = array_filter($itemTypes, function (string $mimeType) {
            return $this->definitionsCollection->isQuestionType($mimeType);
        });

        $builder
            ->add('title', TextType::class)
            ->add('description', TextType::class)
            ->add('content', TextType::class)
            ->add('creator', CreatorType::class)
            ->add('dateCreate', DateType::class)
            ->add('dateModify', DateType::class)
            ->add('mimeType', ChoiceType::class, [
                'choices' => $itemTypes,
                'default' => $itemTypes,
            ])
            ->add('tags', TagType::class)
        ;
    }

    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void
    {
        if (!$this->authorization->isGranted(PlatformRoles::ADMIN)) {
            $queryBuilder
                ->leftJoin(Shared::class, 's', Join::WITH, "{$finder->getAlias()} = s.question")
                ->andWhere("({$finder->getAlias()} = :user OR s.user = :user)")
                ->setParameter('questionUser', $this->tokenStorage->getToken()?->getUser());
        }
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
