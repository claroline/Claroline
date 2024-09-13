<?php

namespace Claroline\AppBundle\API\Finder\Type;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Apply the Public filter to a Finder.
 * It will ensure Anonymous users only get accesses to entities marked as public {@see IsPublic}.
 */
class PublicType extends AbstractType
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'default' => !$this->authorization->isGranted('IS_AUTHENTICATED_FULLY') ?: null,
        ]);

        $resolver->setAllowedValues('default', [null, true, false]);
    }

    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            // force public elements for anonymous
            $value = true;
        } else {
            $requestValue = null;
            if (null !== $finder->getFilterValue()) {
                $requestValue = filter_var($finder->getFilterValue(), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            }

            $value = null === $requestValue ? $options['default'] : $requestValue;
        }

        if (null !== $value) {
            $queryBuilder->andWhere("{$finder->getQueryPath()} = :{$finder->getAlias()}");
            $queryBuilder->setParameter($finder->getAlias(), $value);
        }
    }
}
