<?php

namespace Claroline\AppBundle\API\Finder\Type;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TextType extends AbstractType
{
    /**
     * The partial match uses LIKE %text% to search for fields that contain text.
     */
    public const MODE_PARTIAL = 'partial';

    /**
     * The start match uses LIKE text% to search for fields that start with text.
     */
    public const MODE_START = 'start';

    /**
     * The end match uses LIKE %text% to search for fields that contain text.
     */
    public const MODE_END = 'end';

    /**
     * The exact match uses = text to search for fields that contain text.
     */
    public const MODE_EXACT = 'exact';

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'default' => null,
            'mode' => self::MODE_PARTIAL,
            'trim' => true,
        ]);

        $resolver->setAllowedTypes('default', ['null', 'string']);
        $resolver->setAllowedTypes('trim', 'bool');
        $resolver->setAllowedValues('mode', [self::MODE_PARTIAL, self::MODE_START, self::MODE_END, self::MODE_EXACT]);
    }

    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void
    {
        if ($finder->getSortValue()) {
            $queryBuilder->addOrderBy($finder->getQueryPath(), $finder->getSortValue());
        }

        $value = empty($finder->getFilterValue()) ? $options['default'] : $finder->getFilterValue();
        $value = strtolower($value);
        if ($options['trim']) {
            $value = strtolower($value);
        }

        if (empty($value)) {
            return;
        }

        switch ($options['mode']) {
            case self::MODE_PARTIAL:
                $queryBuilder->andWhere("LOWER({$finder->getQueryPath()}) LIKE :{$finder->getAlias()}");
                $queryBuilder->setParameter($finder->getAlias(), '%'.addcslashes($value, '%_').'%');
                break;

            case self::MODE_START:
                $queryBuilder->andWhere("LOWER({$finder->getQueryPath()}) LIKE :{$finder->getAlias()}");
                $queryBuilder->setParameter($finder->getAlias(), addcslashes($value, '%_').'%');
                break;

            case self::MODE_END:
                $queryBuilder->andWhere("LOWER({$finder->getQueryPath()}) LIKE :{$finder->getAlias()}");
                $queryBuilder->setParameter($finder->getAlias(), '%'.addcslashes($value, '%_'));
                break;

            case self::MODE_EXACT:
                $queryBuilder->andWhere("LOWER({$finder->getQueryPath()}) = :{$finder->getAlias()}");
                $queryBuilder->setParameter($finder->getAlias(), $value);
                break;
        }
    }
}
