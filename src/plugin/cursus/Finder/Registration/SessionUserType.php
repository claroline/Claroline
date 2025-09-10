<?php

namespace Claroline\CursusBundle\Finder\Registration;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Claroline\AppBundle\API\Finder\Type\BooleanType;
use Claroline\AppBundle\API\Finder\Type\ChoiceType;
use Claroline\AppBundle\API\Finder\Type\ClosureType;
use Claroline\AppBundle\API\Finder\Type\DateType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\RelatedEntityType;
use Claroline\CommunityBundle\Finder\UserType;
use Claroline\CursusBundle\Entity\Registration\AbstractRegistration;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SessionUserType extends AbstractType
{
    public const NO_SESSION = 'no_session';
    public const SESSION_CANCELED = 'canceled';
    public const SESSION_NOT_STARTED = 'not_started';
    public const SESSION_IN_PROGRESS = 'in_progress';
    public const SESSION_ENDED = 'ended';
    public const SESSION_NOT_ENDED = 'not_ended';

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SessionUser::class,
            'fulltext' => [],
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', UserType::class)
            ->add('session', RelatedEntityType::class, ['sortBy' => 'startDate'])
            ->add('sessionStatus', ClosureType::class, [
                'buildQuery' => static function (QueryBuilder $queryBuilder, FinderInterface $finder): void {
                    if (null !== $finder->getFilterValue()) {
                        $queryBuilder->leftJoin($finder->getParent()->getAlias().'.session', $finder->getAlias());

                        if (self::SESSION_CANCELED === $finder->getFilterValue()) {
                            $queryBuilder->andWhere("{$finder->getAlias()}.canceled = true");

                            return;
                        } else {
                            $queryBuilder->andWhere("{$finder->getAlias()}.canceled = false");
                        }

                        switch ($finder->getFilterValue()) {
                            case self::NO_SESSION:
                                $queryBuilder->andWhere("{$finder->getAlias()} IS NULL");
                                break;
                            case self::SESSION_NOT_STARTED:
                                $queryBuilder->andWhere("{$finder->getAlias()}.startDate > :{$finder->getAlias()}Now");
                                break;
                            case self::SESSION_IN_PROGRESS:
                                $queryBuilder->andWhere("({$finder->getAlias()}.startDate <= :{$finder->getAlias()}Now AND {$finder->getAlias()}.endDate >= :{$finder->getAlias()}Now)");
                                break;
                            case self::SESSION_ENDED:
                                $queryBuilder->andWhere("({$finder->getAlias()}.endDate IS NOT NULL AND {$finder->getAlias()}.endDate < :{$finder->getAlias()}Now)");
                                break;
                            case self::SESSION_NOT_ENDED:
                                $queryBuilder->andWhere("{$finder->getAlias()} IS NOT NULL");
                                $queryBuilder->andWhere("({$finder->getAlias()}.endDate IS NULL OR {$finder->getAlias()}.endDate >= :{$finder->getAlias()}Now)");
                                break;
                        }

                        if (self::NO_SESSION !== $finder->getFilterValue()) {
                            $queryBuilder->setParameter($finder->getAlias().'Now', new \DateTime());
                        }
                    }
                },
            ])
            ->add('course', RelatedEntityType::class)
            ->add('workspace', ClosureType::class, [
                'buildQuery' => static function (QueryBuilder $queryBuilder, FinderInterface $finder): void {
                    if (null === $finder->getFilterValue()) {
                        return;
                    }

                    $queryBuilder->leftJoin($finder->getParent()->getAlias().'.session', $finder->getAlias());
                    $queryBuilder->leftJoin($finder->getAlias().'.workspace', 'sessionWs');
                    $queryBuilder->leftJoin($finder->getParent()->getAlias().'.course', $finder->getAlias().'Course');
                    $queryBuilder->leftJoin($finder->getAlias().'Course.workspace', 'courseWs');

                    $queryBuilder->andWhere('(sessionWs.uuid = :workspace OR courseWs.uuid = :workspace)');
                    $queryBuilder->setParameter('workspace', $finder->getFilterValue());
                },
            ])
            ->add('date', DateType::class)
            ->add('confirmed', BooleanType::class)
            ->add('validated', BooleanType::class)
            ->add('type', ChoiceType::class, [
                'choices' => [AbstractRegistration::LEARNER, AbstractRegistration::TUTOR],
            ])
        ;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
