<?php

namespace Claroline\AnnouncementBundle\Finder;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Claroline\AppBundle\API\Finder\Type\ClosureType;
use Claroline\AppBundle\API\Finder\Type\CreatorType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\TagType;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\CommunityBundle\Finder\RoleType;
use Claroline\CoreBundle\Finder\WorkspaceType;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnouncementType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Announcement::class,
            'fulltext' => ['title', 'content'],
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('content', TextType::class)
            ->add('creator', CreatorType::class)
            ->add('workspace', WorkspaceType::class)
            ->add('roles', RoleType::class, ['nullable' => true])
            ->add('tags', TagType::class)
            ->add('visible', ClosureType::class, [
                'buildQuery' => static function (QueryBuilder $queryBuilder, FinderInterface $finder): void {
                    if (!$finder->hasFilter()) {
                        return;
                    }

                    $alias = $finder->getAlias();
                    if (!$finder->isRoot()) {
                        $alias = $finder->getParent()->getAlias();
                    }

                    $queryBuilder->andWhere("($alias.visibleFrom >= :now OR $alias.visibleFrom IS NULL)");
                    $queryBuilder->andWhere("($alias.visibleUntil <= :now OR $alias.visibleUntil IS NULL)");
                    $queryBuilder->andWhere("$alias.visible = true");

                    $queryBuilder->setParameter('now', new \DateTime());
                },
            ])
        ;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
